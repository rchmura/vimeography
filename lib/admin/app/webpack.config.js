const ReactRefreshWebpackPlugin = require("@pmmmwh/react-refresh-webpack-plugin");
const webpack = require("webpack");
const path = require("path");
const isProduction = process.env.NODE_ENV === "production";
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

module.exports = {
  mode: isProduction ? "production" : "development",
  devtool: isProduction ? "source-map" : "eval-source-map",
  target: "web",
  entry: {
    index: ["./src/index"],
  },

  output: {
    filename: "[name].[fullhash].js",
    chunkFilename: "[id].chunk.[chunkhash].js",
    path: path.resolve(__dirname, "dist"),
    publicPath: "/",
  },

  externals: {},
  plugins: [],

  resolve: {
    alias: {
      "~": path.resolve(__dirname, "src/"),
    },
    modules: ["node_modules", path.resolve(__dirname, "src")],
    extensions: [".mjs", ".js", ".json", ".jsx", ".tsx", ".ts"],
  },

  module: {
    rules: [
      {
        test: /\.tsx?$/,
        include: path.join(__dirname, "src"),
        use: [
          !isProduction && {
            loader: "babel-loader",
            options: { plugins: ["react-refresh/babel"] },
          },
          {
            loader: "ts-loader",
            options: { transpileOnly: true },
          },
        ].filter(Boolean),
      },

      {
        test: /\.(png|jpg|gif)$/,
        use: ["file-loader"],
      },

      // {
      //   enforce: "pre",
      //   test: /\.js?$/,
      //   use: [
      //     {
      //       loader: "eslint-loader",
      //       options: {
      //         emitWarning: true,
      //         failOnWarning: false,
      //         failOnError: false
      //       }
      //     }
      //   ]
      // },
      {
        test: /\.css$/,
        // exclude: /node_modules/,
        use: [
          "style-loader",
          { loader: "css-loader", options: { importLoaders: 1 } },
          {
            loader: "postcss-loader",
          },
        ],
      },
      {
        test: /\.svg$/,
        loader: "svg-inline-loader",
      },
    ],
  },
};

if (!isProduction) {
  module.exports.output.publicPath = "https://localhost:8024/";
  module.exports.plugins.push(new ReactRefreshWebpackPlugin());
  module.exports.devServer = {
    hot: true,
    overlay: true,
    port: 8024,
    https: true,
    historyApiFallback: true,
    compress: true,
    disableHostCheck: true,
    headers: { "Access-Control-Allow-Origin": "*" },
  };
} else {
  module.exports.plugins.push(
    new CleanWebpackPlugin(),
    new WebpackManifestPlugin()
  );
}
