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
  const [color, setColor] = React.useState(setting.value);
  const ctx = React.useContext(GalleryContext);

  const handleChange = (color) => {
    const colorStr = `rgb(${color.rgb.r} ${color.rgb.g} ${color.rgb.b} / ${
      color.rgb.a
    })`;

    setColor(colorStr);
    ctx.dispatch({
      type: `UPDATE_GALLERY_APPEARANCE`,
      payload: { ...setting, value: colorStr },
    });
  };

  return (
    <div>
      <label className="vm-font-semibold">{setting.label}</label>
      <div
        className="vm-w-8 vm-h-6 vm-border vm-shadow vm-cursor-pointer vm-mb-2"
        style={{ backgroundColor: color }}
        onClick={() => setVisible(!visible)}
      />
      {visible && <ChromePicker color={color} onChange={handleChange} />}
    </div>
  );
};

export default Colorpicker;
