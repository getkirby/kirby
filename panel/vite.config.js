import { defineConfig } from 'vite'
import { createVuePlugin } from 'vite-plugin-vue2'
import pluginRewriteAll from 'vite-plugin-rewrite-all';

const path = require('path');

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
    'process.env.BUILD': JSON.stringify('production')
  },
  build: {
    rollupOptions: {
      output: {
        entryFileNames: `js/[name].js`,
        chunkFileNames: `js/[name].js`,
        assetFileNames: `[ext]/[name].[ext]`
      }
    },
  },
  resolve: {
    alias: [
        { 
          find: "@", 
          replacement: path.resolve(__dirname, 'src')
        }
    ]
  },
  server: {
    proxy: {
      '/api': proxy,
      '/env': proxy,
      '/media': proxy
    },
    ...custom
  }
})
