import * as React from "react";
import { ChromePicker } from "react-color";
import GalleryContext from "~/context/Gallery";
import { ThemeSetting } from "~/providers/Themes";

type ControlProps = {
  setting: ThemeSetting;
};

const Colorpicker = (props: ControlProps) => {
  const setting = props.setting;
  const [visible, setVisible] = React.useState(false);
  const [colorStr, setColorStr] = React.useState(setting.value);
  const ctx = React.useContext(GalleryContext);

  const handleChange = (color) => {
    const colorStr = `rgb(${color.rgb.r} ${color.rgb.g} ${color.rgb.b} / ${
      color.rgb.a
    })`;

    setColorStr(colorStr);

    ctx.dispatch({
      type: `UPDATE_GALLERY_APPEARANCE`,
      payload: { ...setting, value: colorStr },
    });
  };

  return (
    <div>
      <div
        className="vm-flex vm-items-center"
        onClick={() => setVisible(!visible)}
      >
        <div
          className="vm-w-8 vm-h-6 vm-mr-2 vm-border-2 vm-border-white vm-shadow vm-cursor-pointer"
          style={{ backgroundColor: colorStr }}
        />
        <label className="vm-font-semibold">{setting.label}</label>
      </div>

      {visible && (
        <ChromePicker
          color={colorStr}
          onChange={handleChange}
          className="vm-mt-2"
        />
      )}
    </div>
  );
};

export default Colorpicker;
