import { defineConfig } from 'vite'
import createVuePlugin from '@vitejs/plugin-vue'
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
  define: {
    // Fix vuelidate error
    'process.env.BUILD': JSON.stringify('production')
  },
  plugins: [createVuePlugin({
    template: {
      compilerOptions: {
        compatConfig: {
          MODE: 2
        }
      }
    }
  }), pluginRewriteAll()],
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
    alias: {
      '@': path.resolve(__dirname, 'src'),
      vue: '@vue/compat'
    }
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
