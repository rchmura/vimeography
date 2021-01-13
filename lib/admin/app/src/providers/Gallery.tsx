import * as React from "react";
import GalleryContext from "../context/Gallery";
import { useQuery } from "react-query";
import produce from "immer";
import { Helmet } from "react-helmet";
import { useDebouncedCallback } from "use-debounce";

import { Theme, ThemeSetting, ThemeSettingType } from "~/providers/Themes";
import ThemesContext from "../context/Themes";

type GalleryProviderProps = React.PropsWithChildren<{ id?: string }>;

export type GalleryState = {
  id?: string;
  cache_timeout?: string;
  date_created?: string;
  featured_video?: string;
  gallery_width?: string;
  resource_uri?: string;
  source_url?: string;
  theme_name?: string;
  title?: string;
  video_limit?: string;
  appearanceRules?: GalleryAppearanceRule[];
};

interface GalleryResponse {
  id?: string;
  cache_timeout: string;
  date_created: string;
  featured_video: string;
  gallery_width: string;
  resource_uri: string;
  source_url: string;
  theme_name: string;
  title: string;
  video_limit: string;
}

export type GalleryAppearanceRule = {
  id: string;
  css: string;
};

type GalleryAppearancePayload = {
  rules: GalleryAppearanceRule[];
};

type Action =
  | { type: `HYDRATE`; payload: GalleryResponse }
  | { type: `EDIT_GALLERY_STATE`; payload: GalleryState }
  | { type: `UPDATE_GALLERY_APPEARANCE`; payload: ThemeSetting };

const initialState = {
  id: "",
  cache_timeout: "",
  date_created: "",
  featured_video: "",
  gallery_width: "",
  resource_uri: "",
  source_url: "",
  theme_name: "",
  title: "",
  video_limit: "",
  appearanceRules: [],
};

const convertToDashedAttribute = (attr: string) => {
  let shimmedAttribute;

  // backwards-compat for old gallery theme settings
  // removes grid prefix from deprecated theme attributes
  // for browser compatibility
  if (attr === `gridColumnGap`) {
    shimmedAttribute = `columnGap`;
  } else if (attr === `gridRowGap`) {
    shimmedAttribute = `rowGap`;
  } else {
    shimmedAttribute = attr;
  }

  return shimmedAttribute.replace(/[A-Z]/g, (a) => "-" + a.toLowerCase());
};

const computeValue = (
  type: ThemeSettingType,
  transform: string = "",
  value: string
) => {
  const suffix = type === "numeric" || type === "slider" ? "px" : "";

  if (transform !== "") {
    return `${transform.replace(/{{value}}/, value + suffix)}`;
  } else {
    return `${value}${suffix}`;
  }
};

const reducer = (state: GalleryState, action: Action) => {
  switch (action.type) {
    case "HYDRATE":
      return produce(state, (next) => {
        next.id = action.payload.id;
        next.cache_timeout = action.payload.cache_timeout;
        next.date_created = action.payload.date_created;
        next.featured_video = action.payload.featured_video;
        next.gallery_width = action.payload.gallery_width;
        next.resource_uri = action.payload.resource_uri;
        next.source_url = action.payload.source_url;
        next.theme_name = action.payload.theme_name;
        next.title = action.payload.title;
        next.video_limit = action.payload.video_limit;
      });

    case `EDIT_GALLERY_STATE`: {
      return {
        ...state,
        ...action.payload,
      };
    }

    case `UPDATE_GALLERY_APPEARANCE`: {
      let rules: GalleryAppearanceRule[] = [];

      const buildCSS = (
        target: string,
        attribute: string,
        computedValue: string,
        label: string
      ) =>
        `/* ${label}  */
        ${
          namespace ? "#vimeography-gallery-" + state.id : ""
        }${target} { ${attribute}: ${computedValue}${
          important ? " !important;" : ";"
        } }
        `;

      const {
        id,
        label,
        type,
        value,
        properties,
        namespace,
        important,
        expressions = [],
      } = action.payload;

      properties.map((property) => {
        const computedValue = computeValue(type, property.transform, value);
        const attribute = convertToDashedAttribute(property.attribute);

        rules.push({
          id,
          css: buildCSS(property.target, attribute, computedValue, label),
        });
      });

      expressions.map((expression) => {
        const attribute = convertToDashedAttribute(expression.attribute);

        let calculatedValue;

        switch (expression.operator) {
          case "+":
            calculatedValue = Math.ceil(
              parseInt(value) + eval(expression.value)
            );
            break;
          case "-":
            calculatedValue = Math.ceil(
              parseInt(value) - eval(expression.value)
            );
            break;
          case "/":
            calculatedValue = Math.ceil(
              parseInt(value) / eval(expression.value)
            );
            break;
          case "*":
            calculatedValue = Math.ceil(
              parseInt(value) * eval(expression.value)
            );
            break;
        }

        const computedValue = computeValue(
          type,
          expression.transform,
          calculatedValue.toString()
        );

        rules.push({
          id,
          css: buildCSS(expression.target, attribute, computedValue, label),
        });
      });

      return produce(state, (next) => {
        rules.map((rule) => {
          const index = next.appearanceRules.findIndex(
            (el) => el.id === rule.id
          );

          if (index === -1) {
            next.appearanceRules.push(rule);
          } else {
            next.appearanceRules[index] = rule;
          }
        });
      });
    }

    default:
      // console.log(`unknown action type: ${action.type}`);
      return state;
  }
};

const GalleryProvider = (props: GalleryProviderProps) => {
  const themesCtx = React.useContext(ThemesContext);
  const [state, dispatch] = React.useReducer(reducer, initialState);

  const { isLoading, error, data } = useQuery(
    ["getGallery", props.id, dispatch],
    () =>
      fetch(window.wpApiSettings.root + `vimeography/v1/galleries/${props.id}`)
        .then((res) => {
          return res.json();
        })
        .then((payload) => {
          dispatch({ payload, type: "HYDRATE" });
          return payload;
        })
  );

  // Detect any theme CSS customizations on load and
  // send them to the themes context to hydrate
  // the appearance controls with the correct values
  React.useEffect(
    () => {
      if (!data) return;
      if (!themesCtx.data) return;

      const activeTheme: Theme = themesCtx.data.find(
        (theme: Theme) => theme.name === data.theme_name
      );

      if (!activeTheme) return;

      const customStyle = document.getElementById(
        `vimeography-gallery-${props.id}-custom-css`
      );

      if (!customStyle) return;
      console.log(`custom style found!`);
      console.log(customStyle);
      // since it is possible that the element it hidden (as in a modal)
      // try to get the defined value based on the loaded stylesheet
      activeTheme.settings.map((setting) => {
        setting.properties.map((prop) => {
          // build the expected selector
          const selectorToMatch = setting.namespace
            ? `#vimeography-gallery-${props.id}${prop.target}`
            : prop.target;

          // search the stylesheet for the selector
          for (let rule of customStyle.sheet.cssRules) {
            // need to find a match for the target defined in our theme settings

            if (rule.selectorText === selectorToMatch) {
              // get the value for the current prop attribute

              let value = rule.style.getPropertyValue(
                convertToDashedAttribute(prop.attribute)
              );

              // if no value, no customization found for this particular setting
              if (!value) continue;

              // console.log(`value found for ${selectorToMatch}!`);
              // console.log(value);

              // check if it needs to be un-transformed to extract the value
              if (prop.transform) {
                // console.log(
                //   `extracting value from transform ${prop.transform}`
                // );

                const searchTerm = `{{value}}`;

                // find the index of `{{value}}` in the transform
                const tokenIndex = prop.transform.indexOf(searchTerm);

                // find the characters which appear after the searchTerm in the transform
                const terminator = prop.transform.substr(
                  tokenIndex + searchTerm.length
                );

                // get the index of the terminating characters within the existing customized css value
                const terminatorIndex = value.indexOf(terminator);

                // pull the value out of the string
                const extractedValue = value.substring(
                  tokenIndex,
                  terminatorIndex
                );

                // console.log(
                //   `extracted ${extractedValue} as the value based on the transform`
                // );

                value = extractedValue;
              }

              // reformat the incoming `action.payload.value` to the expected format for the ui control based on the settings type `setting.type`

              let computedValue;

              // console.log(setting);

              switch (setting.type) {
                case "colorpicker": {
                  // { r: 51, g: 51, b: 51, a: 1 } react-color may already convert this for us, test!
                  computedValue = value;
                  break;
                }

                case "numeric":
                case "slider": {
                  // must be a number
                  const strippedValue = value.replace("px", "");
                  const convertedValue = Math.ceil(strippedValue);

                  computedValue = convertedValue;
                  break;
                }

                default: {
                  computedValue = value;
                  break;
                }
              }

              console.log(
                `updating value for ${setting.id} to ${computedValue}`
              );

              // update the default `activeTheme`s `setting.value` for `setting.id` to the determined configured `value`
              themesCtx.dispatch({
                type: `THEME.SETTING.UPDATE_DEFAULT_VALUE`,
                payload: {
                  themeName: activeTheme.name,
                  settingId: setting.id,
                  value: computedValue,
                },
              });

              dispatch({
                type: `UPDATE_GALLERY_APPEARANCE`,
                payload: { ...setting, value: computedValue },
              });
            }
          }
        });
      });

      // Remove the style that came from the server since we'll be
      // generating a new one in the client anyway
      customStyle.remove();
    },
    [data, themesCtx]
  );

  const saveCSS = useDebouncedCallback(
    // function
    async (stylesheetId) => {
      const styles = document.getElementById(stylesheetId).innerText;
      console.log({ styles });

      const response = await fetch(
        window.wpApiSettings.root +
          `vimeography/v1/galleries/${props.id}/appearance`,
        {
          method: "POST",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.wpApiSettings.nonce,
            // 'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: JSON.stringify({ css: styles }), // body data type must match "Content-Type" header
        }
      );

      console.log(response.ok);
    },
    // delay in ms
    2500
  );

  React.useEffect(
    () => {
      if (!data) return;

      console.log(`appearanceRules changed, debouncing and syncing…`);
      saveCSS.callback(`vimeography-gallery-${props.id}-custom-css-preview`);
    },
    [state.appearanceRules]
  );

  return (
    <GalleryContext.Provider
      value={{
        isLoading,
        error,
        data,
        state,
        dispatch,
      }}
    >
      <Helmet>
        <script type="text/javascript">{`!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});`}</script>
        <script type="text/javascript">{`window.Beacon('init', 'f7cb9d02-1ec7-4320-ad10-fcbc85e4d544')`}</script>
        <style
          type="text/css"
          id={`vimeography-gallery-${props.id}-custom-css-preview`}
        >{`
      ${state.appearanceRules.map((rule) => rule.css).join("\r\n\r\n")}
    `}</style>
      </Helmet>

      {props.children}
    </GalleryContext.Provider>
  );
};

export default GalleryProvider;
