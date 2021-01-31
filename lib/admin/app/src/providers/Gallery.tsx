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
  id?: number;
  cache_timeout?: number;
  date_created?: string;
  featured_video?: string;
  gallery_width?: string;
  resource_uri?: string;
  source_url?: string;
  theme_name?: string;
  title?: string;
  video_limit?: number;
  appearanceRules?: GalleryAppearanceRule[];
  allow_downloads?: boolean;
  sort?: SortType;
  direction?: SortDirection;
  enable_search?: boolean;
  enable_tags?: boolean;
  enable_playlist?: boolean;
  per_page?: number;
};

type SortDirection = "asc" | "desc";
type SortType =
  | "date"
  | "likes"
  | "comments"
  | "plays"
  | "alphabetical"
  | "duration"
  | "default";
interface GalleryResponse {
  id: number;
  cache_timeout: number;
  date_created: string;
  featured_video: string;
  gallery_width: string;
  resource_uri: string;
  source_url: string;
  theme_name: string;
  title: string;
  video_limit: number;
  allow_downloads?: boolean;
  sort?: SortType;
  direction?: SortDirection;
  enable_search?: boolean;
  enable_tags?: boolean;
  enable_playlist?: boolean;
  per_page?: number;
}

export type GalleryAppearanceRule = {
  id: string;
  css: string;
};

type Action =
  | { type: `HYDRATE`; payload: GalleryResponse }
  | { type: `EDIT_GALLERY_STATE`; payload: GalleryState }
  | { type: `RESET_GALLERY_APPEARANCE` }
  | { type: `UPDATE_GALLERY_APPEARANCE`; payload: ThemeSetting };

const initialState = {
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
    case `EDIT_GALLERY_STATE`: {
      return {
        ...state,
        ...action.payload,
      };
    }

    case `RESET_GALLERY_APPEARANCE`: {
      return produce(state, (next) => {
        next.appearanceRules = [];
      });
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

      properties.map((property, index) => {
        const computedValue = computeValue(type, property.transform, value);
        const attribute = convertToDashedAttribute(property.attribute);

        rules.push({
          id: `${id}-property-${index}`,
          css: buildCSS(property.target, attribute, computedValue, label),
        });
      });

      expressions.map((expression, index) => {
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
          id: `${id}-expression-${index}`,
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
    [`galleries`, props.id],
    () => {
      return fetch(
        window.vimeographyApiSettings.root +
          `vimeography/v1/galleries/${props.id}`
      ).then((res) => {
        return res.json();
      });
    },
    { staleTime: Infinity }
  );

  React.useEffect(() => {
    if (!data) return;
    dispatch({ payload: data, type: "HYDRATE" });
  }, [data]);

  // Detect any theme CSS customizations on load and
  // send them to the themes context to hydrate
  // the appearance controls with the correct values
  React.useEffect(() => {
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
    console.log(`rules:`);
    console.dir(customStyle.sheet.cssRules);
    // since it is possible that the element it hidden (as in a modal)
    // try to get the defined value based on the loaded stylesheet
    activeTheme.settings.map((setting) => {
      setting.properties.map((prop) => {
        // build the expected selector

        // accounts for incorrect single-colon pseudo selectors in any theme settings
        // https://regex101.com/r/zzlv1J/1
        const modifiedTarget = prop.target.replace(
          /([^:])(:)([^:])/g,
          "$1::$3"
        );

        const selectorToMatch = setting.namespace
          ? `#vimeography-gallery-${props.id}${modifiedTarget}`
          : modifiedTarget;

        console.log(
          `attempting to match theme setting in custom stylesheet rules: ${selectorToMatch}`
        );

        // search the stylesheet for the selector
        for (let rule of customStyle.sheet.cssRules) {
          // need to find a match for the target defined in our theme settings

          if (rule.selectorText === selectorToMatch) {
            // get the value for the current prop attribute

            console.log(`we have a match`);

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

            console.log(`updating value for ${setting.id} to ${computedValue}`);

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
  }, [data, themesCtx]);

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
