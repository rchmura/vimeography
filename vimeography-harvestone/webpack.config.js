const webpack = require("webpack");
const path = require("path");
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { VueLoaderPlugin } = require('vue-loader')
const postcssPresetEnv = require('postcss-preset-env');

const isProduction = process.env.NODE_ENV === "production";

module.exports = {
  devtool: isProduction ? "cheap-module-source-map" : "eval-source-map",
  devServer: {
    port: 8153,
    static: {
      publicPath: process.env.VIMEOGRAPHY_HARVESTONE_DEV_SERVER_URL || "http://localhost:8153/",
      directory: "./dist",
    },
    hot: true,
    allowedHosts: "all",
    historyApiFallback: true,
    headers: {
      "Access-Control-Allow-Origin": "*",
    }
  },
  target: "web",
  entry: "./src/index",

  output: {
    filename: isProduction ? "scripts.[hash:8].js" : "scripts.js",
    path: path.resolve(__dirname, "dist"),
    publicPath: ""
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules\/(?!(dom7|swiper)\/).*/,
        loader: "babel-loader",
      },
      {
        test: /\.vue$/,
        use: [
          'vue-loader',
        ],
        exclude: /node_modules/,
      },
      {
        test: /\.(scss|css)$/,
        use: [
          'vue-style-loader',
          {
            loader: "css-loader",
            options: {
              sourceMap: true,
              importLoaders: 1
            },
          },
          {
            loader: "postcss-loader",
            options: {
              postcssOptions: {
                plugins: [
                  require('postcss-nested'),
                  [
                    "postcss-preset-env",
                    {
                      // Options
                    },
                  ],
                ],
              },
            },
          },
        ],
      },
    ],
  },

  resolve: {
    alias: {
      vue$: "vue/dist/vue.esm.js",
    },
    modules: ['node_modules', path.resolve(__dirname, '../../..')],
  },

  plugins: [
    new VueLoaderPlugin(),
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: isProduction ? "styles.[hash:8].css" : "styles.css",
    }),
    new WebpackManifestPlugin(),
  ],
};