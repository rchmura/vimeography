import * as React from "react";
import { NavLink, Route, Switch } from "react-router-dom";
import GalleryContext from "~/context/Gallery";

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
    <div className="vm-p-4">
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
    </div>
  );
};

export default BasicSettings;
