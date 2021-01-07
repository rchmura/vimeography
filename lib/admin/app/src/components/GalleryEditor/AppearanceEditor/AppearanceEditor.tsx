import * as React from "react";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";

import { Theme } from "~/providers/Themes";

import Colorpicker from "./Colorpicker";
import NumericControl from "./Numeric";
import SliderControl from "./Slider";
import VisibilityControl from "./Visibility";
import Label from "./Label";

const AppearanceEditor = () => {
  const gallery = React.useContext(GalleryContext);
  const themes = React.useContext(ThemesContext);

  if (themes.isLoading) return "Loading…";

  const activeTheme: Theme = themes.data.find(
    (theme: Theme) => theme.name === gallery.data.theme_name
  );

  return (
    <div>
      <div className="vm-px-4 vm-py-5 vm-border-b vm-border-gray-200 vm-relative">
        <div
          className="vm-absolute vm-inset-0 vm-opacity-70"
          style={{
            background: `url(${activeTheme.thumbnail}) no-repeat`,
            backgroundSize: `cover`,
            filter: `blur(3px)`,
          }}
        />
        <div className="vm-z-10 vm-relative">
          <Label>Current gallery theme</Label>

          <div className="vm-flex vm-items-center">
            {/* <img
              src={activeTheme.thumbnail}
              className="vm-w-12 vm-border-4 vm-border-white vm-mr-2 vm-rounded vm-shadow"
            /> */}
            <div
              className="vm-font-semibold vm-text-white vm-text-3xl vm-flex-1"
              style={{ textShadow: `rgb(28 31 35 / 40%) 0px 2px 2px` }}
            >
              {activeTheme.name}
            </div>
            <button className="vm-bg-white vm-px-2 vm-py-1 vm-rounded vm-text-gray-700 vm-flex vm-items-center vm-text-sm vm-border">
              <svg
                className="vm-w-4 vm-h-4 vm-mr-1"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z" />
              </svg>
              Switch theme…
            </button>
          </div>
        </div>
      </div>

      {activeTheme.settings.map((control) => {
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
          <div
            key={control.id}
            className="vm-px-4 vm-py-5 vm-border-b vm-border-gray-200"
          >
            <Control setting={control} />
          </div>
        );
      })}
    </div>
  );
};

export default AppearanceEditor;
