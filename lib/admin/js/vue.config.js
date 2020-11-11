module.exports = {
  productionSourceMap: false,
  runtimeCompiler: true,
  publicPath:
    process.env.NODE_ENV === "production"
      ? "/wp-content/plugins/vimeography/dist/"
      : "http://localhost:8080/",
  outputDir: "../dist",
  chainWebpack: (config) => {
    config
      .entry("pro")
      .add("../../../../vimeography-pro/lib/admin/js/src/main.js")
      .end();
  },
  configureWebpack: {
    devServer: {
      contentBase: "/wp-content/plugins/vimeography/dist/",
      allowedHosts: ["gallerylab.local"],
      headers: {
        "Access-Control-Allow-Origin": "*",
      },
    },
    externals: {
      jquery: "jQuery",
    },
    output: {
      filename: "js/[name].js",
      chunkFilename: "js/[name].js",
    },
  },
  css: {
    extract: false,
    requireModuleExtension: false,
  },
};
