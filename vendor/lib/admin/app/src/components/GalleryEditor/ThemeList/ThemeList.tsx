import * as React from "react";
import { Link } from "react-router-dom";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";

const ThemeList = () => {
  const gallery = React.useContext(GalleryContext);
  const themes = React.useContext(ThemesContext);

  const formRef = React.useRef();
  const themeFieldRef = React.useRef();

  const handleClick = (themeName) => {
    themeFieldRef.current.value = themeName;
    formRef.current.submit();
  };

  return (
    <div>
      <Link
        to="/appearance"
        className="vm-text-blue-500 vm-p-4 vm-flex vm-items-center vm-no-underline"
      >
        <svg
          className="vm-w-4 vm-h-4 vm-mr-1"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clipRule="evenodd"
          />
        </svg>
        Back to appearance editor
      </Link>
      <form ref={formRef} method="post" action="">
        <input
          type="hidden"
          name="vimeography-theme-verification"
          value={window.vimeographyThemeNonce}
        />
        <input type="hidden" name="theme_name" value="" ref={themeFieldRef} />
        <input
          type="hidden"
          name="vimeography-action"
          value="set_gallery_theme"
        />
      </form>
      <div className="vm-max-h-96 vm-overflow-scroll">
        {themes.data.map((theme) => {
          const isActiveTheme =
            theme.name.toLowerCase() === gallery.data.theme_name.toLowerCase();

          return (
            <div
              key={theme.name}
              onClick={() => handleClick(theme.name)}
              className={`vm-group vm-flex vm-items-center vm-px-4 vm-py-2 vm-cursor-pointer vm-border-b vm-transition ${
                isActiveTheme ? "vm-bg-indigo-500" : "hover:vm-bg-indigo-600"
              }`}
            >
              <img
                src={theme.thumbnail}
                className="vm-w-10 vm-mr-3 vm-rounded vm-shadow"
              />
              <div>
                <span
                  className={`vm-font-semibold vm-block vm-text-sm group-hover:vm-text-white ${
                    isActiveTheme ? "vm-text-white" : "vm-text-gray-700"
                  }`}
                >
                  {theme.name}
                </span>
                <span
                  className={`vm-text-xs group-hover:vm-text-gray-50 ${
                    isActiveTheme ? "vm-text-gray-50" : "vm-text-gray-400"
                  }`}
                >
                  v{theme.version}
                </span>
                {!isActiveTheme && (
                  <span
                    className={`vm-text-xs vm-opacity-0 group-hover:vm-opacity-100 group-hover:vm-text-gray-50 "vm-text-gray-400"
                    }`}
                  >
                    {" "}
                    • Switch to {theme.name}
                  </span>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default ThemeList;
