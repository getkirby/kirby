import { defineConfig } from 'vite'
import { createVuePlugin } from 'vite-plugin-vue2'

const path = require('path');
const host = process.env.VUE_APP_DEV_SERVER || "http://sandbox.test";
const proxy = {
  target: host,
  changeOrigin: true,
  secure: false
};

export default defineConfig({
  plugins: [createVuePlugin()],
  define: {
    // Fix vuelidate error
    'process.env.BUILD': JSON.stringify('production')
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "./src/index";` 
     },
    },
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
        {find: "@", replacement: path.resolve(__dirname, 'src')}
    ]
  },
  server: {
    proxy: {
      '/api': proxy,
      '/env': proxy,
      '/media': proxy
    }
  }
})
