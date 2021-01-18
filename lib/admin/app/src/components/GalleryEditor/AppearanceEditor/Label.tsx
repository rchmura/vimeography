import * as React from "react";

const Label = (props) => {
  return (
    <div className="vm-font-semibold vm-mb-2 vm-text-sm vm-text-gray-600">
      {props.children}
    </div>
  );
};

export default Label;
