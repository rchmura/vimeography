import * as React from "react";
import GalleryContext from "../context/Gallery";
import { useQuery } from "react-query";
import { produce } from "immer";
import { Helmet } from "react-helmet";

import { ThemeSetting, ThemeSettingType } from "~/providers/Themes";

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

const convertToDashedAttribute = (attr: string) =>
  attr.replace(/[A-Z]/g, (a) => "-" + a.toLowerCase());

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
  const [state, dispatch] = React.useReducer(reducer, initialState);

  document.getElementById(`vimeography-gallery-${props.id}-custom-css`)?.remove();

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
        <style type="text/css">{`
      ${state.appearanceRules.map((rule) => rule.css).join("\r\n\r\n")}
    `}</style>
      </Helmet>

      {props.children}
    </GalleryContext.Provider>
  );
};

export default GalleryProvider;
