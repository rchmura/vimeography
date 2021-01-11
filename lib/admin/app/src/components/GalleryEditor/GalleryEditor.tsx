import * as React from "react";
import { NavLink, Route, Switch, useRouteMatch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";
import ThemesContext from "~/context/Themes";

import AppearanceEditor from "./AppearanceEditor/AppearanceEditor";
import BasicSettings from "./BasicSettings/BasicSettings";
import ThemeList from "./ThemeList/ThemeList";

import { motion } from "framer-motion";

const NavItem = (props) => (
  <NavLink
    to={props.to}
    exact
    className="vm-bg-gray-50 vm-flex vm-items-center vm-px-4 vm-py-3 vm-text-gray-700 vm-outline-none hover:no-underline focus:vm-outline-none focus:vm-no-underline vm-font-semibold"
    activeClassName="vm-bg-white vm-border-l-4 vm-border-indigo-700 vm-outline-none focus:vm-outline-none focus:vm-no-underline"
  >
    {props.children}
  </NavLink>
);

const Menu = () => {
  const variants = {
    open: { height: "auto", opacity: 1 },
    closed: { height: "0", opacity: 0 },
  };

  const isSettingsPanel = useRouteMatch({ path: "/", strict: true });
  const isAppearancePanel = useRouteMatch({
    path: "/appearance",
    strict: true,
  });

  return (
    <>
      <NavItem to="/">
        <svg
          className="vm-w-5 vm-h-5 vm-mr-2 vm-text-blue-600"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
            clipRule="evenodd"
          />
        </svg>
        <span>Settings</span>
      </NavItem>

      <motion.div
        className="vm-overflow-hidden"
        animate={isSettingsPanel.isExact ? "open" : "closed"}
        variants={variants}
        transition={{ duration: 0.25, ease: "easeOut", bounce: 0 }}
      >
        <BasicSettings />
      </motion.div>

      <NavItem to="/appearance">
        <svg
          className="vm-w-5 vm-h-5 vm-mr-2 vm-text-green-600"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z"
            clipRule="evenodd"
          />
        </svg>
        <span>Appearance</span>
      </NavItem>
      <motion.div
        className="vm-overflow-hidden"
        animate={isAppearancePanel ? "open" : "closed"}
        variants={variants}
        transition={{ duration: 0.25, ease: "easeOut", bounce: 0 }}
      >
        <Switch>
          <Route path="/appearance/themes">
            <ThemeList />
          </Route>
          <Route path="/appearance" strict>
            <AppearanceEditor />
          </Route>
        </Switch>
      </motion.div>
    </>
  );
};

const GalleryEditor = () => {
  const ctx = React.useContext(GalleryContext);

  if (ctx.isLoading) return <div>Loading…</div>;

  return (
    <div className="vm-bg-gray-100 vm-rounded vm-border vm-border-gray-200 vm-mt-5 vm-sticky vm-top-10">
      <div className="vm-p-4 vm-bg-indigo-700 vm-rounded-t">
        <h2 className="vm-text-lg vm-text-white vm-font-bold">
          {ctx.state.title}
        </h2>
        <a href={ctx.state.source_url} className="vm-text-indigo-50 vm-text-sm">
          {ctx.state.source_url}
        </a>
      </div>

      <Menu />
      <div>
        <div className="vm-m-4 vm-flex vm-justify-end">
          <button className="vm-bg-blue-600 vm-text-white vm-px-3 vm-py-2 vm-rounded">
            Save changes
          </button>
        </div>
      </div>
    </div>
  );
};

export default GalleryEditor;
