/* eslint-env node */
const path = require("path");
const fs = require("fs");

// vue.config.js
module.exports = {
  css: {
    loaderOptions: {
      sass: {
        prependData: fs.readFileSync(
          "./src/main.scss",
          "utf-8"
        )
      }
    }
  },
  productionSourceMap: false,
  configureWebpack: () => {
    let custom = {
      resolve: {
        modules: [path.resolve("./node_modules")],
        alias: {
          vue$: "vue/dist/vue.esm.js"
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
  devServer: {
    proxy: {
      "/api": {
        target: process.env.VUE_APP_DEV_SERVER || "http://kir.by"
      }
    }
  }
};
