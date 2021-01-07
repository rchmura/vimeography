import * as React from "react";

import Slider from "rc-slider";
import "rc-slider/assets/index.css";

import { AppearanceControl } from "./AppearanceEditor";

const SliderControl = (props: AppearanceControl) => {
  return (
    <div className="vm-px-4 vm-mb-4">
      <div className="vm-font-semibold vm-mb-2">{props.label}</div>
      <Slider />
    </div>
  );
};

export default SliderControl;
