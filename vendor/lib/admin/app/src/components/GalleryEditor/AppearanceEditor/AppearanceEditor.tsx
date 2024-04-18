import * as React from "react";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";
import { Link } from "react-router-dom";

import { Theme } from "~/providers/Themes";

import Colorpicker from "./Colorpicker";
import NumericControl from "./Numeric";
import SliderControl from "./Slider";
import VisibilityControl from "./Visibility";

const AppearanceEditor = () => {
  const gallery = React.useContext(GalleryContext);
  const themesCtx = React.useContext(ThemesContext);

  if (themesCtx.isLoading) return <div>Loading…</div>;

  const activeTheme: Theme = themesCtx.state.themes.find(
    (theme: Theme) => theme.name === gallery.data.theme_name
  );

  return (
    <div className="vm-overflow-scroll" style={{ maxHeight: `30rem` }}>
      <div className="vm-py-5 vm-border-b vm-border-gray-200 vm-relative">
        <div
          className="vm-absolute vm-inset-0 vm-opacity-90"
          style={{
            background: `linear-gradient(to left, rgb(230, 100, 101), rgb(145 152 229 / 56%)), url(${
              activeTheme.thumbnail
            }) no-repeat 100% 100%`,
          }}
        />
        <div className="vm-z-10 vm-relative">
          <div className="vm-flex vm-items-center vm-px-4 vm-py-2">
            <div className="vm-flex-1">
              <div
                className="vm-text-white"
                style={{ textShadow: `rgb(28 31 35 / 40%) 0px 2px 2px` }}
              >
                Current gallery theme
              </div>

              <div
                className="vm-font-semibold vm-text-white vm-text-2xl"
                style={{ textShadow: `rgb(28 31 35 / 40%) 0px 2px 2px` }}
              >
                {activeTheme.name}
              </div>
            </div>

            <Link
              to="/appearance/themes"
              className="vm-bg-white vm-px-2 vm-py-1 vm-rounded vm-text-gray-700 vm-flex vm-items-center vm-text-xs vm-border vm-no-underline"
            >
              <svg
                className="vm-w-4 vm-h-4 vm-mr-1"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z" />
              </svg>
              Switch theme…
            </Link>
          </div>
        </div>
      </div>

      <button
        className="vm-p-4 vm-text-blue-500 vm-flex vm-items-center hover:vm-underline vm-border-0 vm-bg-transparent vm-cursor-pointer"
        onClick={() => {
          gallery.dispatch({ type: `RESET_GALLERY_APPEARANCE` });
          themesCtx.dispatch({ type: `RESET`, payload: themesCtx.data });
        }}
      >
        <svg
          className="vm-w-4 vm-h-4 vm-mr-1"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
        Reset appearance
      </button>

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
