import * as React from "react";
import { ChromePicker } from "react-color";
import { AppearanceControl } from "./AppearanceEditor";

const Colorpicker = (props: AppearanceControl) => {
  const [visible, setVisible] = React.useState(false);
  const [color, setColor] = React.useState(props.value);

  const handleChange = (color) => {
    const colorStr = `rgb(${color.rgb.r} ${color.rgb.g} ${color.rgb.b} / ${
      color.rgb.a
    })`;
    setColor(colorStr);

    // var attr = prop.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
    // var rule = {};
    // rule[attr] = e.value;

    // vein.inject( prop.target, rule );
  };

  return (
    <div className="vm-p-5">
      <label className="vm-font-semibold">{props.label}</label>
      <div
        className="vm-w-8 vm-h-5 vm-border vm-shadow vm-cursor-pointer vm-mb-2"
        style={{ backgroundColor: color }}
        onClick={() => setVisible(!visible)}
      />
      {visible && <ChromePicker color={color} onChange={handleChange} />}
    </div>
  );
};

export default Colorpicker;
