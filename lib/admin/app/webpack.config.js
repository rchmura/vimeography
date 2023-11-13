const ReactRefreshWebpackPlugin = require("@pmmmwh/react-refresh-webpack-plugin");
const webpack = require("webpack");
const path = require("path");
const isProduction = process.env.NODE_ENV === "production";
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const ModuleFederationPlugin = require("webpack/lib/container/ModuleFederationPlugin");

module.exports = {
  mode: isProduction ? "production" : "development",
  devtool: isProduction ? "source-map" : "eval-source-map",
  target: "web",
  entry: {
    index: ["./src/index"],
  },

  output: {
    filename: isProduction ? "[name].[fullhash].js" : "[name].js",
    chunkFilename: "[id].chunk.[chunkhash].js",
    path: path.resolve(__dirname, "dist"),
  },

  externals: {},
  plugins: [
    new ModuleFederationPlugin({
      name: "vimeography",
      filename: "remoteEntry.js",
      remotes: {
        // vimeography_pro:
        //   "vimeography_pro@https://localhost:8094/remoteEntry.js", // wish we could set the remoteEntry url based on a registered window variable here…
        vimeography_pro: "vimeography_pro@", // script is loaded via wp_enqueue_script, NOT webpack. https://github.com/module-federation/module-federation-examples/issues/518
      },
      shared: ["react", "react-dom", "react-router-dom", "react-query"],
    }),
  ],

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
            options: {
              plugins: ["react-refresh/babel"],
            },
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
  const devServerPath = process.env.VIMEOGRAPHY_ADMIN_DEV_SERVER_URL || 'http://localhost:8024';
  module.exports.output.publicPath = `${devServerPath}/`;
  module.exports.plugins.push(new ReactRefreshWebpackPlugin());
  
  let devServerOpts = {
    hot: true,
    client: {
      overlay: true,
    },
    host: '0.0.0.0',
    port: 8024,
    historyApiFallback: true,
    allowedHosts: "all",
    headers: { "Access-Control-Allow-Origin": "*" },
  };
  
  if (process.env.VIMEOGRAPHY_ADMIN_DEV_SERVER_URL) {
    devServerOpts.client.webSocketURL = {
      hostname: process.env.VIMEOGRAPHY_ADMIN_DEV_SERVER_URL.replace("https://", ""),
      pathname: "/ws",
      port: 443,
      protocol: "wss",
    };
  }

  console.log(devServerOpts);
  
  module.exports.devServer = devServerOpts;
} else {
  module.exports.plugins.push(
    new CleanWebpackPlugin(),
    new WebpackManifestPlugin({ publicPath: "" })
  );
}
