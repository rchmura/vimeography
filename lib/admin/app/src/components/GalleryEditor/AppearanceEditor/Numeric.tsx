import * as React from "react";
import InputNumber from "rc-input-number";
import "rc-input-number/assets/index.css";

import { AppearanceControl } from "./AppearanceEditor";

const NumericControl = (props: AppearanceControl) => {
  return (
    <div className="vm-px-4 vm-mb-4">
      <div className="vm-font-semibold vm-mb-2">{props.label}</div>
      <InputNumber
        defaultValue={props.value}
        min={props.min}
        max={props.max}
        step={props.step}
        formatter={(val) => `${val}px`}
      />
    </div>
  );
};

export default NumericControl;
