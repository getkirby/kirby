import { defineConfig } from "vite";
import { createVuePlugin } from "vite-plugin-vue2";
import pluginRewriteAll from "vite-plugin-rewrite-all";
import postcssLogical from "postcss-logical";
import postcssDirPseudoClass from "postcss-dir-pseudo-class";

const path = require("path");

let custom;
try {
  custom = require("./vite.config.custom.js");
} catch (e) {
  custom = {};
}

const proxy = {
  target: process.env.VUE_APP_DEV_SERVER || "http://sandbox.test",
  changeOrigin: true,
  secure: false
};

export default defineConfig({
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
        postcssLogical({ preserve: true }),
        postcssDirPseudoClass()
      ]
    }
  },
  resolve: {
    alias: [
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
})
