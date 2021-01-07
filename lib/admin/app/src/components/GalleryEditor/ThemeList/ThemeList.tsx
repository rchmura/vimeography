import * as React from "react";
import { NavLink, Route, Switch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";

const ThemeList = () => {
  const gallery = React.useContext(GalleryContext);
  const themes = React.useContext(ThemesContext);

  return (
    <div>
      {themes.data.map((theme) => {
        const isActiveTheme =
          theme.name.toLowerCase() === gallery.data.theme_name.toLowerCase();

        return (
          <div
            className={`vm-group vm-flex vm-items-center vm-px-4 vm-py-2 vm-cursor-pointer vm-border-b ${
              isActiveTheme ? "vm-bg-indigo-500" : "hover:vm-bg-indigo-600"
            }`}
          >
            <img
              src={theme.thumbnail}
              className="vm-w-10 vm-mr-3 vm-rounded vm-shadow"
            />
            <div>
              <span className="vm-font-semibold vm-block vm-text-sm vm-text-gray-700 group-hover:vm-text-white">
                {theme.name}
              </span>
              <span className="vm-text-xs vm-text-gray-400">
                v{theme.version}
              </span>
            </div>
          </div>
        );
      })}
    </div>
  );
};

export default ThemeList;
