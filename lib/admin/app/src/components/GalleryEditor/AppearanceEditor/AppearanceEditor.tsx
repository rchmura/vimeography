import * as React from "react";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";

import { Theme } from "~/providers/Themes";

import Colorpicker from "./Colorpicker";
import NumericControl from "./Numeric";
import SliderControl from "./Slider";
import VisibilityControl from "./Visibility";

/** Defines which CSS selectors and properties that the setting will control.
 An array of one or more arrays, with each array containing two key/value pairs: */
export type AppearanceControlProperty = {
  /** defines the CSS property that this setting will control for the corresponding target selector. */
  attribute: string;

  /** defines the CSS selector that the setting will affect */
  target: string;

  /** allows you to provide a string with a {{value}} token to define where the resulting pixel value should be injected in the generated CSS string value. */
  transform?: string;
};

/** Contains all of the configurable settings for a theme. */
export type AppearanceControl = {
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
  properties: AppearanceControlProperty[];

  /** The UI control to render for the current setting. */
  type: "colorpicker" | "slider" | "numeric" | "visibility";

  /**
   * Defines additional CSS selectors and properties that the setting will control,
   * but this time, relatively manipulating the value before associating it with the
   * selector. This is useful if you have two selectors whose values are linked and
   * change relative to one another (widescreen image ratios, margins etc.)
   */
  expressions?: AppearanceControlExpression[];

  /** If set to TRUE, the CSS rule will be saved with an `!important` flag. */
  important?: boolean;

  /** [required if `type` is 'slider' or 'numeric'] The minimum value that a CSS property can be set. */

  min?: number;

  /** [required if `type` is 'slider' or 'numeric'] The maximum value that a CSS property can be set. */

  max?: number;

  /** [required if `type` is 'slider' or 'numeric'] The increment/decrement value of the UI control. */
  step?: number;
};

/**
 * Defines additional CSS selectors and properties that the setting will control,
 * but this time, relatively manipulating the value before associating it with the
 * selector. This is useful if you have two selectors whose values are linked and
 * change relative to one another (widescreen image ratios, margins etc.)
 */
export type AppearanceControlExpression = {
  /** defines the CSS selector that the setting will affect */
  target: string;
  /** the CSS property that this setting will control for the target selector. */
  attribute: string;
  /** defines the symbol(s) to use for the mathmatical operation to perform on the original setting value. */
  operator: string;
  /** is the input integer which acts as the addend, subtrahend, divisor, multiplier etc. to the original setting value. */
  value: string;
};

const AppearanceEditor = () => {
  const gallery = React.useContext(GalleryContext);
  const themes = React.useContext(ThemesContext);

  const activeTheme = themes.data.find(
    (theme: Theme) => theme.name === gallery.data.theme_name
  );

  return (
    <div>
      Now using: {activeTheme.name}
      Change theme…
      {activeTheme.settings.map((control: AppearanceControl) => {
        let Control = null;

        if (control.type === "colorpicker") {
          Control = Colorpicker;
        } else if (control.type === "numeric") {
          Control = NumericControl;
        } else if (control.type === "slider") {
          Control = SliderControl;
        } else if (control.type === "visibility") {
          Control = VisibilityControl;
        } else {
          return null;
        }

        return (
          <div key={control.id}>
            <Control {...control} />
          </div>
        );
      })}
    </div>
  );
};

export default AppearanceEditor;
