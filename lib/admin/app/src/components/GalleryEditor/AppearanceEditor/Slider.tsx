import * as React from "react";

import Slider from "rc-slider";
import "rc-slider/assets/index.css";

import GalleryContext from "~/context/Gallery";
import { ThemeSetting } from "~/providers/Themes";

type ControlProps = {
  setting: ThemeSetting;
};

const SliderControl = (props: ControlProps) => {
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
      <Slider
        value={parseInt(value)}
        onChange={handleChange}
        min={parseInt(setting.min)}
        max={parseInt(setting.max)}
        step={parseInt(setting.step)}
      />
    </div>
  );
};

export default SliderControl;
