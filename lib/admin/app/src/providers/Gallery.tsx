import * as React from "react";
import GalleryContext from "../context/Gallery";
import { useQuery } from "react-query";
import { produce } from "immer";
import { Helmet } from "react-helmet";

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

type Action =
  | { type: `HYDRATE`; payload: GalleryResponse }
  | { type: `EDIT_GALLERY_STATE`; payload: GalleryState };

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

    default:
      console.log(`unknown action type: ${action.type}`);
      return state;
  }
};

const GalleryProvider = (props: GalleryProviderProps) => {
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
        body {
            background-color: blue;
        }

        p {
            font-size: 12px;
        }
    `}</style>
      </Helmet>

      {props.children}
    </GalleryContext.Provider>
  );
};

export default GalleryProvider;
