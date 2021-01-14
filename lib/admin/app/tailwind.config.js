const colors = require("tailwindcss/colors");

module.exports = {
  purge: ["./src/**/*.js", "./src/**/*.jsx", "./src/**/*.tsx"],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        gray: colors.coolGray,
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
