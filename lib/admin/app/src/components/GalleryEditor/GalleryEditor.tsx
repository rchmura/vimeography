import * as React from "react";
import { NavLink, Route, Switch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";

const Menu = () => {
  return (
    <>
      <NavLink
        to="/settings"
        className="vm-flex vm-items-center vm-px-4 vm-py-3 hover:vm-bg-gray-200 hover:vm-text-gray-700 hover:no-underline focus:vm-text-white focus:vm-outline-none focus:vm-no-underline"
        activeClassName="vm-bg-gray-800 vm-text-white"
      >
        <svg
          className="vm-w-5 vm-h-5 vm-mr-2"
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
      </NavLink>
      <NavLink
        to="/appearance"
        className="vm-flex vm-items-center vm-px-4 vm-py-3 hover:vm-bg-gray-200 hover:vm-text-gray-700 hover:no-underline focus:vm-text-white focus:vm-outline-none focus:vm-no-underline"
        activeClassName="vm-bg-gray-800 vm-text-white"
      >
        <svg
          className="vm-w-5 vm-h-5 vm-mr-2"
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
      </NavLink>
    </>
  );
};

const Setting = ({ children }) => <div className="vm-mb-4">{children}</div>;

const SettingLabel = ({ children }) => (
  <label className="vm-font-semibold vm-text-gray-700">{children}</label>
);

const BasicSettings = () => {
  const ctx = React.useContext(GalleryContext);

  const handleUpdate = (payload) => {
    ctx.dispatch({
      type: `EDIT_GALLERY_STATE`,
      payload,
    });
  };

  return (
    <>
      <Setting>
        <SettingLabel>Refresh the videos every</SettingLabel>
        {/* Specifies how frequently Vimeography should check your Vimeo source for any new videos that may have been added. */}
        <select
          value={ctx.state.cache_timeout}
          onChange={(e) => handleUpdate({ cache_timeout: e.target.value })}
        >
          <option value="0">page load</option>
          <option value="900">15 minutes</option>
          <option value="1800">30 minutes</option>
          <option value="3600">hour</option>
          <option value="86400">day</option>
          <option value="604800">week</option>
          <option value="2419200">month</option>
        </select>

        <button
          onClick={() => {
            // {{admin_url}}edit-galleries&id={{id}}&vimeography-action=refresh_gallery_cache
            return false;
          }}
        />
      </Setting>
      <Setting>
        <SettingLabel>Number of videos</SettingLabel>
        <input
          type="text"
          value={ctx.state.video_limit}
          onChange={(e) => handleUpdate({ video_limit: e.target.value })}
        />
        <p className="vm-text-xs vm-text-gray-400">
          Specifies the number of videos that will appear in your gallery. Set
          to 0 for the maximum amount. You can display a maximum of up to 25
          videos.
        </p>
      </Setting>
      <Setting>
        <SettingLabel>Max gallery width</SettingLabel>
        <input
          type="text"
          placeholder="eg. 960px, 35%"
          value={ctx.state.gallery_width}
          onChange={(e) => handleUpdate({ gallery_width: e.target.value })}
        />
        <p className="vm-text-xs vm-text-gray-400">
          Specifies the maximum width that your gallery container can expand to.
        </p>
      </Setting>
      <Setting>
        <SettingLabel>Featured video URL</SettingLabel>
        <input
          type="text"
          placeholder="eg. https://vimeo.com/3567483"
          value={ctx.state.featured_video}
          onChange={(e) => handleUpdate({ featured_video: e.target.value })}
        />
        <p className="vm-text-xs vm-text-gray-400">
          Sets the specified video as the first video that appears in your
          gallery.
        </p>
      </Setting>
    </>
  );
};

const AppearanceSettings = () => {
  const ctx = React.useContext(GalleryContext);
  return <div>{ctx.state.theme_name}</div>;
};

const GalleryEditor = () => {
  const ctx = React.useContext(GalleryContext);

  return (
    <div className="vm-bg-gray-100 vm-rounded vm-border vm-border-gray-200">
      <div className="vm-p-4">
        <h2 className="vm-text-lg vm-text-gray-600">{ctx.state.title}</h2>
        <a className="vm-text-blue-600">{ctx.state.source_url}</a>
      </div>

      <Menu />
      <div className="vm-p-4">
        <Switch>
          <Route path="/settings">
            <BasicSettings />{" "}
          </Route>
          <Route path="/appearance">
            <AppearanceSettings />
          </Route>
          <Route>
            <div>nomatch</div>
          </Route>
        </Switch>
      </div>
    </div>
  );
};

export default GalleryEditor;
