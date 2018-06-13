const path = require("path");
const fs = require("fs");

// vue.config.js
module.exports = {
  css: {
    loaderOptions: {
      sass: {
        data: fs.readFileSync(
          "./node_modules/getkirby-ui/src/main.scss",
          "utf-8"
        )
      }
    }
  },
  configureWebpack: () => {
    let custom = {
      resolve: {
        modules: [path.resolve("./node_modules")],
        alias: {
          vue$: "vue/dist/vue.esm.js",
          "@ui": "getkirby-ui/src"
        }
      }
    };

    if (process.env.NODE_ENV === "production") {
      custom.output = {
        filename: "js/[name].js"
      };
    }

    return custom;
  },
  chainWebpack: config => {
    config.when(process.env.NODE_ENV === "production", plugin => {
      plugin
        .plugin("extract-css")
        .tap(([options, ...args]) => [
          Object.assign({}, options, { filename: "css/[name].css" }),
          ...args
        ]);
    });
  },
  devServer: {
    proxy: {
      "/api": {
        target: process.env.VUE_APP_DEV_SERVER || "http://kir.by"
      }
    }
  }
};
