import * as React from "react";
import { NavLink, Route, Switch, useRouteMatch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";
import { useNotification } from "~/hooks/useNotification";
import AppearanceEditor from "./AppearanceEditor/AppearanceEditor";
import BasicSettings from "./BasicSettings/BasicSettings";
import ThemeList from "./ThemeList/ThemeList";
import { motion } from "framer-motion";

import { ErrorBoundary } from "react-error-boundary";
const ProSettings = React.lazy(() => import("vimeography_pro/ProSettings"));
import ProSellPanel from "./ProSellPanel";

const NavItem = (props) => (
  <NavLink
    to={props.to}
    exact
    className="vm-bg-gray-50 vm-border-b vm-no-underline vm-flex vm-items-center vm-px-4 vm-py-3 vm-text-gray-700 vm-outline-none hover:no-underline focus:vm-outline-none focus:vm-no-underline vm-font-semibold"
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

  const galleryCtx = React.useContext(GalleryContext);
  const isProPanel = useRouteMatch({ path: "/pro", strict: true });

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
      <NavItem to="/pro">
        <svg
          className="vm-w-5 vm-h-5 vm-mr-2 vm-text-yellow-600"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
            clipRule="evenodd"
          />
        </svg>
        <span>Vimeography Pro</span>
      </NavItem>
      <motion.div
        className="vm-overflow-hidden"
        animate={isProPanel ? "open" : "closed"}
        variants={variants}
        transition={{ duration: 0.25, ease: "easeOut", bounce: 0 }}
      >
        <ErrorBoundary FallbackComponent={ProSellPanel}>
          <React.Suspense fallback={<p>Loading Vimeography Pro…</p>}>
            <ProSettings isProPanel={isProPanel} galleryCtx={galleryCtx} />
          </React.Suspense>
        </ErrorBoundary>
      </motion.div>
    </>
  );
};

const Skeleton = () => {
  const [phrase, set] = React.useState(`Loading…`);

  React.useEffect(() => {
    const cb = () => {
      const phrases = [
        `Be right there…`,
        `Churning the butter…`,
        `Petting cute doggies…`,
        `Checking the fridge again…`,
        `Flossing…`,
        `Loading…`,
        `Waiting in line…`,
        `On my way…`,
        `Ready in a sec…`,
        `Coming in for landing…`,
        `Adding final touches…`,
        `Quick restroom visit…`,
        `Pouring coffee…`,
        `Traffic on 77N…`,
        `Stuck behind a slow walker…`,
        `Waiting for rain to stop…`,
        `Going as fast as I can…`,
        `Almost got it…`,
      ];
      const phrase = phrases[Math.floor(Math.random() * phrases.length)];
      set(phrase);
    };

    const task = setInterval(cb, 2000);

    return () => {
      clearInterval(task);
    };
  }, []);

  return (
    <div
      className="vm-bg-gray-200 vm-animate-pulse vm-w-full vm-flex vm-flex-col vm-items-center vm-justify-center vm-rounded vm-border vm-border-gray-200 vm-mt-5 vm-sticky vm-top-10 vm-text-indigo-600"
      style={{
        height: `30rem`,
        backgroundImage: `linear-gradient(16deg, rgba(116, 116, 116,0.02) 0%, rgba(116, 116, 116,0.02) 25%,transparent 25%, transparent 96%,rgba(177, 177, 177,0.02) 96%, rgba(177, 177, 177,0.02) 100%),linear-gradient(236deg, rgba(148, 148, 148,0.02) 0%, rgba(148, 148, 148,0.02) 53%,transparent 53%, transparent 59%,rgba(56, 56, 56,0.02) 59%, rgba(56, 56, 56,0.02) 100%),linear-gradient(284deg, rgba(16, 16, 16,0.02) 0%, rgba(16, 16, 16,0.02) 46%,transparent 46%, transparent 71%,rgba(181, 181, 181,0.02) 71%, rgba(181, 181, 181,0.02) 100%),linear-gradient(316deg, rgba(197, 197, 197,0.02) 0%, rgba(197, 197, 197,0.02) 26%,transparent 26%, transparent 49%,rgba(58, 58, 58,0.02) 49%, rgba(58, 58, 58,0.02) 100%),linear-gradient(90deg, rgb(255,255,255),rgb(255,255,255))`,
      }}
    >
      <svg
        className="vm-w-6 vm-h-6 vm-animate-spin vm-text-indigo-800 vm-mb-2"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fillRule="evenodd"
          d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z"
          clipRule="evenodd"
        />
      </svg>
      <div className="vm-text-xs">{phrase}</div>
    </div>
  );
};

const GalleryEditor = () => {
  const ctx = React.useContext(GalleryContext);
  const [submitting, setSubmitting] = React.useState(false);
  const { showNotification } = useNotification();

  const handleSubmit = async () => {
    setSubmitting(true);

    try {
      const {
        id,
        resource_uri,
        date_created,
        appearanceRules,
        ...payload
      } = ctx.state;

      const styles = document.getElementById(
        `vimeography-gallery-${ctx.data.id}-custom-css-preview`
      ).innerText;
      console.log({ styles });

      const responseA = await fetch(
        window.vimeographyApiSettings.root +
          `vimeography/v1/galleries/${ctx.data.id}/appearance`,
        {
          method: "POST",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.wpApiSettings
              ? window.wpApiSettings.nonce
              : window.vimeographyApiSettings.nonce,
            // 'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: JSON.stringify({ css: styles }), // body data type must match "Content-Type" header
        }
      );

      console.log(`styles saved: ${responseA.ok}`);

      const response = await fetch(
        window.vimeographyApiSettings.root +
          `vimeography/v1/galleries/${ctx.data.id}`,
        {
          method: "PATCH",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.wpApiSettings
              ? window.wpApiSettings.nonce
              : window.vimeographyApiSettings.nonce,
            // 'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: JSON.stringify(payload), // body data type must match "Content-Type" header
        }
      );

      await response.json();
      showNotification(`success`, "Saved! Reloading gallery…");
    } catch (error) {
      console.log(error);
      showNotification(`error`, "Whoops! We hit a snag saving your settings.");
    } finally {
      // setSubmitting(false);
      location.reload();
    }
  };

  if (ctx.isLoading) return <Skeleton />;

  return (
    <div className="vm-bg-gray-100 vm-rounded vm-border vm-border-gray-200 vm-mt-5 vm-sticky vm-top-10">
      <div className="vm-p-4 vm-bg-indigo-700 vm-rounded-t">
        <h2 className="vm-text-lg vm-text-white vm-font-bold vm-my-0">
          {ctx.state.title}
        </h2>
        <a href={ctx.state.source_url} className="vm-text-indigo-50 vm-text-sm">
          {ctx.state.source_url}
        </a>
      </div>

      <Menu />
      <div className="vm-flex vm-justify-end">
        <button
          className="vm-bg-blue-600 hover:vm-bg-blue-700 vm-m-4 vm-text-white vm-px-3 vm-py-2 vm-rounded vm-border-0 vm-cursor-pointer"
          onClick={handleSubmit}
          disabled={submitting}
        >
          {submitting ? "Saving…" : "Save changes"}
        </button>
      </div>
    </div>
  );
};

export default GalleryEditor;
