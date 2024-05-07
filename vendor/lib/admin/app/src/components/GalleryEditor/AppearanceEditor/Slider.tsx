import * as React from "react";

import { Range } from "react-range";

import GalleryContext from "~/context/Gallery";
import { ThemeSetting } from "~/providers/Themes";

type ControlProps = {
  setting: ThemeSetting;
};

const SliderControl = (props: ControlProps) => {
  const setting = props.setting;
  const [value, setValue] = React.useState(parseInt(setting.value));
  const ctx = React.useContext(GalleryContext);

  const handleChange = (values: number[]) => {
    setValue(values[0]);
    ctx.dispatch({
      type: `UPDATE_GALLERY_APPEARANCE`,
      payload: { ...setting, value: values[0] },
    });
  };

  return (
    <div>
      <div className="vm-font-semibold vm-mb-3">{setting.label}</div>

      <Range
        step={parseInt(setting.step)}
        min={parseInt(setting.min)}
        max={parseInt(setting.max)}
        values={[parseInt(value)]}
        onChange={handleChange}
        renderTrack={({ props, children }) => (
          <div
            {...props}
            style={{
              ...props.style,
            }}
            className="vm-w-full vm-h-1 vm-bg-gray-200 vm-rounded-xl"
          >
            {children}
          </div>
        )}
        renderThumb={({ props, isDragged }) => (
          <div
            {...props}
            style={{
              ...props.style,
            }}
            className={`vm-flex vm-items-center vm-justify-center vm-bg-blue-600 vm-rounded-full vm-w-4 vm-h-4`}
          >
            <div
              className={`vm-absolute vm-text-xs vm-px-2 vm-py-1 vm-rounded vm--top-8 vm-text-white vm-bg-blue-600 vm-z-10 ${
                isDragged ? "vm-block" : "vm-hidden"
              }`}
            >
              {value}px
            </div>
          </div>
        )}
      />
    </div>
  );
};

export default SliderControl;
