import * as React from "react";
import Toggle from "react-toggle";
import "react-toggle/style.css"; // for ES6 modules

import { AppearanceControl } from "./AppearanceEditor";

const VisibilityControl = (props: AppearanceControl) => {
  return (
    <div className="vm-px-4 vm-mb-4">
      <label className="vm-flex vm-items-center">
        <Toggle defaultChecked={false} onChange={() => {}} />
        <span className="vm-font-semibold vm-mb-2 vm-ml-2">{props.label}</span>
      </label>
    </div>
  );
};

export default VisibilityControl;
