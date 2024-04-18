const colors = require("tailwindcss/colors");

module.exports = {
  corePlugins: {
    preflight: false, // disables any global resets in wordpress admin
  },
  content: ["./src/**/*.js", "./src/**/*.jsx", "./src/**/*.tsx"],
  media: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        gray: colors.gray,
      },
    },
  },
  variants: {
    extend: {
      backgroundColor: ["odd"],
    },
  },
  plugins: [],
  prefix: "vm-",
};
