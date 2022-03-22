/* eslint-env node */
import fs from "fs";
import path from "path";
import { defineConfig } from "vite";
import { createVuePlugin } from "vite-plugin-vue2";
import postcssAutoprefixer from "autoprefixer";
import postcssCsso from "postcss-csso";
import postcssDirPseudoClass from "postcss-dir-pseudo-class";
import postcssLogical from "postcss-logical";
import pluginRewriteAll from "vite-plugin-rewrite-all";

let custom;
try {
  custom = require("./vite.config.custom.js");
} catch (err) {
  custom = {};
}

export default defineConfig(({ command }) => {
  // Tell Kirby that we are in dev mode
  if (command === "serve") {
    // Create the flag file on start
    const runningPath = __dirname + "/.vite-running";
    fs.closeSync(fs.openSync(runningPath, "w"));

    // Delete the flag file on any kind of exit
    for (const eventType of ["exit", "SIGINT", "uncaughtException"]) {
      process.on(eventType, function (err) {
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
      minify: "terser",
      cssCodeSplit: false,
      rollupOptions: {
        input: "./src/index.js",
        output: {
          entryFileNames: "js/[name].js",
          chunkFileNames: "js/[name].js",
          assetFileNames: "[ext]/[name].[ext]"
        }
      }
    },
    optimizeDeps: {
      entries: "src/**/*.{js,vue}",
      exclude: [
        "vitest"
      ]
    },
    css: {
      postcss: {
        plugins: [
          postcssLogical(),
          postcssDirPseudoClass(),
          postcssCsso(),
          postcssAutoprefixer()
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
    },
    test: {
      environment: "jsdom",
      include: ["**/*.test.js"],
      coverage: {
        all: true,
        exclude: ["**/*.e2e.js", "**/*.test.js"],
        extension: ["js", "vue"],
        src: "src",
        reporter: ["text", "lcov"]
      },
      setupFiles: ["vitest.setup.js"]
    }
  };
});
