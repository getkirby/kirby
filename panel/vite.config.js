/* eslint-env node */
import path from "path";

import { defineConfig, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue2";
import { viteStaticCopy } from "vite-plugin-static-copy";
import externalGlobals from "rollup-plugin-external-globals";

import postcssAutoprefixer from "autoprefixer";
import postcssCsso from "postcss-csso";
import postcssDirPseudoClass from "postcss-dir-pseudo-class";
import postcssLogical from "postcss-logical";

import kirbyDev from "./scripts/vite-kirby-dev.js";

let customServer;
try {
	customServer = require("./vite.config.custom.js");
} catch (err) {
	customServer = {};
}

export default defineConfig(({ command }) => {
	// gather plugins depending on environment
	const plugins = [vue(), splitVendorChunkPlugin(), kirbyDev()];

	if (command === "build") {
		plugins.push(
			viteStaticCopy({
				targets: [
					{
						src: "node_modules/vue/dist/vue.min.js",
						rename: "vue.js",
						dest: "js"
					}
				]
			})
		);
	}

	if (!process.env.VITEST) {
		plugins.push(
			// Externalize Vue so it's not loaded from node_modules
			// but accessed via window.Vue
			{
				...externalGlobals({ vue: "Vue" }),
				enforce: "post"
			}
		);
	}

	const proxy = {
		target: process.env.VUE_APP_DEV_SERVER ?? "http://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	return {
		plugins,
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
			exclude: ["vitest", "vue"]
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
			open: proxy.target + "/panel",
			port: 3000,
			...customServer
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
