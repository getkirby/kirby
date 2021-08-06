import { createVuePlugin } from "vite-plugin-vue2";
import pluginRewriteAll from "vite-plugin-rewrite-all";
import postcssCsso from "postcss-csso";
import postcssLogical from "postcss-logical";
import postcssDirPseudoClass from "postcss-dir-pseudo-class";

const fs      = require("fs");
const path    = require("path");
const process = require("process");

let custom;
try {
  custom = require("./vite.config.custom.js");
} catch (e) {
  custom = {};
}

export default ({ command }) => {
  // Tell Kirby that we are in dev mode
  if (command === "serve") {
    // Create the flag file on start
    const runningPath = __dirname + "/.vite-running";
    fs.closeSync(fs.openSync(runningPath, "w"));

    // Delete the flag file on any kind of exit
    for (let eventType of ["exit", "SIGINT", "uncaughtException"]) {
      process.on(eventType, function(err) {
        if (fs.existsSync(runningPath) === true) {
          fs.unlinkSync(runningPath);
        }

        if (eventType === "uncaughtException") {
          console.error(err);
        }

        process.exit();
      });
    }
  }

  const proxy = {
    target: process.env.VUE_APP_DEV_SERVER || "http://sandbox.test",
    changeOrigin: true,
    secure: false
  };

  return {
    plugins: [createVuePlugin(), pluginRewriteAll()],
    define: {
      // Fix vuelidate error
      "process.env.BUILD": JSON.stringify("production")
    },
    build: {
      cssCodeSplit: false,
      rollupOptions: {
        input: '/src/index.js',
        output: {
          entryFileNames: `js/[name].js`,
          chunkFileNames: `js/[name].js`,
          assetFileNames: `[ext]/[name].[ext]`
        }
      },
    },
    optimizeDeps: {
      entries: "src/**/*.{js,vue}"
    },
    css: {
      postcss: {
        plugins: [
          postcssLogical(),
          postcssDirPseudoClass(),
          postcssCsso()
        ]
      }
    },
    resolve: {
      alias: [
          {
            find: "vue",
            replacement: "vue/dist/vue.esm.js"
          },
          {
            find: "@",
            replacement: path.resolve(__dirname, "src")
          }
      ]
    },
    server: {
      proxy: {
        "/api": proxy,
        "/env": proxy,
        "/media": proxy
      },
      ...custom
    }
  };
};
