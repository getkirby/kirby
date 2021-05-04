import { defineConfig } from 'vite'
import { createVuePlugin } from 'vite-plugin-vue2'

const path = require('path');

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
      external: [
        '/media/plugins/index.css',
        '/media/plugins/index.js',
      ],
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
      '/api': {
        target: 'http://sandbox.test/',
        changeOrigin: true,
        secure: false
      },
      '/env': {
        target: 'http://sandbox.test/',
        changeOrigin: true,
        secure: false
      },
      '/media': {
        target: 'http://sandbox.test/',
        changeOrigin: true,
        secure: false
      }
    }
  }
})
