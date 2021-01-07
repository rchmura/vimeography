import * as React from "react";
import ThemesContext from "../context/Themes";
import { useQuery } from "react-query";
import { produce } from "immer";

type ThemesProviderProps = React.PropsWithChildren<{ id?: string }>;

export type Theme = {
  name?: string;
  description: string;
  version: string;
  thumbnail: string;
  is_licensed: string;
  settings: any;
};

export type ThemesState = {
  themes?: Theme[] | [];
};

type Action = { type: `HYDRATE`; payload: Theme[] };

const initialState: ThemesState = {
  themes: [],
};

const reducer = (state: ThemesState, action: Action) => {
  switch (action.type) {
    case "HYDRATE":
      return produce(state, (next) => {
        next.themes = action.payload;
      });

    default:
      console.log(`unknown action type: ${action.type}`);
      return state;
  }
};

const ThemesProvider = (props: ThemesProviderProps) => {
  const [state, dispatch] = React.useReducer(reducer, initialState);

  const { isLoading, error, data } = useQuery(`getThemes`, () =>
    fetch(window.wpApiSettings.root + `vimeography/v1/themes`)
      .then((res) => {
        return res.json();
      })
      .then((payload) => {
        dispatch({ payload, type: "HYDRATE" });
        return payload;
      })
  );

  return (
    <ThemesContext.Provider
      value={{
        isLoading,
        error,
        data,
        state,
        dispatch,
      }}
    >
      {props.children}
    </ThemesContext.Provider>
  );
};

export default ThemesProvider;
