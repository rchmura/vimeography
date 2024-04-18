import * as React from "react";
import { NavLink, Route, Switch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";

const Setting = ({ children }) => <div className="vm-mb-4">{children}</div>;

const SettingLabel = ({ children }) => (
  <label className="vm-font-semibold vm-text-gray-700 vm-block vm-mb-1">
    {children}
  </label>
);

const BasicSettings = () => {
  const ctx = React.useContext(GalleryContext);

  const [isRefreshing, setIsRefreshing] = React.useState(false);

  const handleUpdate = (payload) => {
    ctx.dispatch({
      type: `EDIT_GALLERY_STATE`,
      payload,
    });
  };

  return (
    <div className="vm-p-4">
      <Setting>
        <SettingLabel>Refresh the videos every</SettingLabel>
        {/* Specifies how frequently Vimeography should check your Vimeo source for any new videos that may have been added. */}

        <div className="vm-flex vm-items-center vm-justify-between">
          <select
            className=""
            value={ctx.state.cache_timeout}
            onChange={(e) =>
              handleUpdate({ cache_timeout: parseInt(e.target.value) })
            }
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
            className="vm-flex vm-items-center vm-text-blue-500 vm-outline-none focus:vm-outline-none vm-border-0 vm-bg-transparent vm-cursor-pointer"
            onClick={() => {
              setIsRefreshing(true);
              const url =
                window.location.href +
                `&vimeography-action=refresh_gallery_cache`;
              window.location.href = url;
            }}
          >
            <svg
              className={`vm-w-4 vm-h-4 vm-mr-1 ${
                isRefreshing ? "vm-animate-spin" : ""
              }`}
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fillRule="evenodd"
                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                clipRule="evenodd"
              />
            </svg>
            {isRefreshing ? "Refreshing…" : "Force refresh"}
          </button>
        </div>
      </Setting>
      <Setting>
        <SettingLabel>Number of videos</SettingLabel>
        <input
          type="number"
          min="0"
          max="25"
          value={ctx.state.video_limit}
          onChange={(e) =>
            handleUpdate({ video_limit: parseInt(e.target.value) })
          }
          className="vm-mb-2 vm-w-24"
        />
        <p className="vm-text-xs vm-text-gray-400">
          Specifies the number of videos that will appear in your gallery. Set
          to 0 for the maximum amount. You can display unlimited videos with{" "}
          <a
            className="vm-text-blue-500"
            href="https://vimeography.com/pro"
            target="_blank"
          >
            Vimeography Pro.
          </a>
        </p>
      </Setting>
      <Setting>
        <SettingLabel>Max gallery width</SettingLabel>
        <input
          type="text"
          placeholder="eg. 960px, 35%"
          value={ctx.state.gallery_width}
          onChange={(e) => handleUpdate({ gallery_width: e.target.value })}
          className="vm-mb-2"
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
          className="vm-mb-2 vm-w-full"
        />
        <p className="vm-text-xs vm-text-gray-400">
          Sets the specified video as the first video that appears in your
          gallery.
        </p>
      </Setting>
    </div>
  );
};

export default BasicSettings;
