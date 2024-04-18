import * as React from "react";
import { ThemesState } from "../providers/Themes";

type ThemesContext = {
  isLoading: boolean;
  error: Error;
  data: ThemesState;
  state: ThemesState;
  dispatch: any;
};

const ctx: ThemesContext = {
  isLoading: true,
  error: null,
  data: null,
  state: null,
  dispatch: () => {},
};

const ThemesContext = React.createContext(ctx);

export default ThemesContext;
