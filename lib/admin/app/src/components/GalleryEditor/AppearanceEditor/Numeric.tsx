import * as React from "react";
import InputNumber from "rc-input-number";
import "rc-input-number/assets/index.css";

import { ThemeSetting } from "~/providers/Themes";
import GalleryContext from "~/context/Gallery";

type ControlProps = {
  setting: ThemeSetting;
};

const NumericControl = (props: ControlProps) => {
  const setting = props.setting;
  const [value, setValue] = React.useState(parseInt(setting.value));
  const ctx = React.useContext(GalleryContext);

  const handleChange = (value: number) => {
    setValue(value);
    ctx.dispatch({
      type: `UPDATE_GALLERY_APPEARANCE`,
      payload: { ...setting, value },
    });
  };

  return (
    <div>
      <div className="vm-font-semibold vm-mb-2">{setting.label}</div>
      <InputNumber
        value={value}
        defaultValue={setting.value}
        min={setting.min}
        max={setting.max}
        step={setting.step}
        formatter={(val) => `${val}px`}
        onChange={handleChange}
      />
    </div>
  );
};

export default NumericControl;
