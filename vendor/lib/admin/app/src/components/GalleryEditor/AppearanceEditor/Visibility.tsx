import * as React from "react";
import Toggle from "react-toggle";
import "react-toggle/style.css"; // for ES6 modules

import GalleryContext from "~/context/Gallery";
import { ThemeSetting } from "~/providers/Themes";

type ControlProps = {
  setting: ThemeSetting;
};

const VisibilityControl = (props: ControlProps) => {
  const setting = props.setting;

  // uses the default value from the theme settings.php file
  const [checked, setChecked] = React.useState(
    setting.value === "block" ? true : false
  );

  const ctx = React.useContext(GalleryContext);

  const handleChange = (e) => {
    const isChecked = e.target.checked;
    setChecked(isChecked);
    ctx.dispatch({
      type: `UPDATE_GALLERY_APPEARANCE`,
      payload: { ...setting, value: isChecked ? `block` : `none` },
    });
  };

  return (
    <div>
      <label className="vm-flex vm-items-center">
        <Toggle checked={checked} onChange={handleChange} />
        <span className="vm-font-semibold vm-mb-2 vm-ml-2 vm-transform vm-translate-y-1">
          {setting.label}
        </span>
      </label>
    </div>
  );
};

export default VisibilityControl;
