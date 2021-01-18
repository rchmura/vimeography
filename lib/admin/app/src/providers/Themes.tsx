import * as React from "react";
import ThemesContext from "../context/Themes";
import { useQuery } from "react-query";
import produce from "immer";

type ThemesProviderProps = React.PropsWithChildren<{ id?: string }>;

/** Defines which CSS selectors and properties that the setting will control.
 An array of one or more arrays, with each array containing two key/value pairs: */
export type ThemeSettingProperty = {
  /** defines the CSS property that this setting will control for the corresponding target selector. */
  attribute: string;

  /** defines the CSS selector that the setting will affect */
  target: string;

  /** allows you to provide a string with a {{value}} token to define where the resulting pixel value should be injected in the generated CSS string value. */
  transform?: string;
};

/**
 * Defines additional CSS selectors and properties that the setting will control,
 * but this time, relatively manipulating the value before associating it with the
 * selector. This is useful if you have two selectors whose values are linked and
 * change relative to one another (widescreen image ratios, margins etc.)
 */
export type ThemeSettingExpression = {
  /** defines the CSS selector that the setting will affect */
  target: string;
  /** the CSS property that this setting will control for the target selector. */
  attribute: string;
  /** defines the symbol(s) to use for the mathmatical operation to perform on the original setting value. */
  operator: ThemeSettingExpressionOperation;
  /** is the input integer which acts as the addend, subtrahend, divisor, multiplier etc. to the original setting value. */
  value: string;
  /** allows you to provide a string with a {{value}} token to define where the resulting pixel value should be injected in the generated CSS string value. */
  transform?: string;
};

type ThemeSettingExpressionOperation = "+" | "-" | "/" | "*";

export type ThemeSettingType =
  | "colorpicker"
  | "slider"
  | "numeric"
  | "visibility";

/** Contains all of the configurable settings for a theme. */
export type ThemeSetting = {
  /** An arbitrary identifier string to associate with the UI control's form field. */
  id: string;

  /** The i18n-compatible label for this particular setting. */
  label: string;

  /**
   * Whether or not the DOM element being targeted by the CSS is a child of the
   * vimeography-gallery-{{gallery_id}} container. Usually TRUE, unless your theme
   * uses a fancybox plugin, in which case, the modal window is outside of the container
   * element, so FALSE would be appropriate.
   */
  namespace: boolean;

  /** Whether or not this setting requires the Vimeography Pro plugin to be installed. TRUE if `type` is 'colorpicker', otherwise FALSE. */
  pro: boolean;

  /** The default CSS value for this setting. */
  value: string;

  /** Defines which CSS selectors and properties that the setting will control. */
  properties: ThemeSettingProperty[];

  /** The UI control to render for the current setting. */
  type: ThemeSettingType;

  /**
   * Defines additional CSS selectors and properties that the setting will control,
   * but this time, relatively manipulating the value before associating it with the
   * selector. This is useful if you have two selectors whose values are linked and
   * change relative to one another (widescreen image ratios, margins etc.)
   */
  expressions?: ThemeSettingExpression[];

  /** If set to TRUE, the CSS rule will be saved with an `!important` flag. */
  important?: boolean;

  /** [required if `type` is 'slider' or 'numeric'] The minimum value that a CSS property can be set. */

  min?: number;

  /** [required if `type` is 'slider' or 'numeric'] The maximum value that a CSS property can be set. */

  max?: number;

  /** [required if `type` is 'slider' or 'numeric'] The increment/decrement value of the UI control. */
  step?: number;
};

export type Theme = {
  name?: string;
  description: string;
  version: string;
  thumbnail: string;
  is_licensed: string;
  settings: ThemeSetting[];
};

export type ThemesState = {
  themes?: Theme[] | [];
};

type UpdateThemeSettingDefaultValuePayload = {
  themeName: string;
  settingId: string;
  value: any;
};

type Action =
  | { type: `HYDRATE`; payload: Theme[] }
  | { type: `RESET`; payload: Theme[] }
  | {
      type: `THEME.SETTING.UPDATE_DEFAULT_VALUE`;
      payload: UpdateThemeSettingDefaultValuePayload;
    };

const initialState: ThemesState = {
  themes: [],
};

const reducer = (state: ThemesState, action: Action) => {
  switch (action.type) {
    case "HYDRATE": {
      if (state.themes.length > 0) return state; //only hydrate once

      return produce(state, (next) => {
        next.themes = action.payload;
      });
    }

    case "RESET": {
      return produce(state, (next) => {
        next.themes = action.payload;
      });
    }

    case `THEME.SETTING.UPDATE_DEFAULT_VALUE`: {
      // console.log(action.payload);
      const themeIndex = state.themes.findIndex(
        (theme) => theme.name === action.payload.themeName
      );
      const settingIndex = state.themes[themeIndex].settings.findIndex(
        (setting) => setting.id === action.payload.settingId
      );

      const nextState = produce(state, (draft) => {
        draft.themes[themeIndex].settings[settingIndex].value =
          action.payload.value;
      });

      return nextState;
    }

    default:
      console.log(`unknown action type: ${action.type}`);
      return state;
  }
};

const ThemesProvider = (props: ThemesProviderProps) => {
  const [state, dispatch] = React.useReducer(reducer, initialState);

  const { isLoading, error, data } = useQuery(`getThemes`, () =>
    fetch(window.vimeographyApiSettings.root + `vimeography/v1/themes`)
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
