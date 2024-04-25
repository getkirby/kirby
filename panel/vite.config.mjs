/* eslint-env node */
import path from "path";

import { defineConfig, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import kirby from "./scripts/vite-kirby.mjs";

/**
 * Returns all aliases used in the project
 */
function createAliases() {
	return {
		"@": path.resolve(__dirname, "src"),
		vue: "vue/dist/vue.esm-bundler.js"
	};
}

/**
 * Returns the server configuration
 */
function createServer() {
	const proxy = {
		target: process.env.VUE_APP_DEV_SERVER ?? "http://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	return {
		proxy: {
			"/api": proxy,
			"/env": proxy,
			"/media": proxy
		},
		open: proxy.target + "/panel",
		port: 3000,
		...createCustomServer()
	};
}

/**
 * Returns custom server configuration, if it exists
 */
function createCustomServer() {
	try {
		return require("./vite.config.custom.js");
	} catch {
		return {};
	}
}

/**
 * Returns an array of plugins used,
 * depending on the mode (development or build)
 */
function createPlugins(mode) {
	const plugins = [vue(), splitVendorChunkPlugin(), kirby()];

	// when building…
	if (mode === "build") {
		//copy Vue to the dist directory
		plugins.push(
			viteStaticCopy({
				targets: [
					{
						src: "node_modules/vue/dist/vue.esm-browser.js",
						dest: "js"
					},
					{
						src: "node_modules/vue/dist/vue.esm-browser.prod.js",
						dest: "js"
					}
				]
			})
		);
	}

	return plugins;
}

/**
 * Returns vitest configuration
 */
function createTest() {
	return {
		environment: "node",
		include: ["**/*.test.js"],
		setupFiles: ["vitest.setup.js"]
	};
}

/**
 * Returns the Vite configuration
 */
export default defineConfig(({ command }) => {
	return {
		plugins: createPlugins(command),
		base: "./",
		build: {
			minify: "terser",
			cssCodeSplit: false,
			rollupOptions: {
				external: ["vue"],
				input: "./src/index.js",
				output: {
					entryFileNames: "js/[name].min.js",
					chunkFileNames: "js/[name].min.js",
					assetFileNames: "[ext]/[name].min.[ext]"
				}
			}
		},
		optimizeDeps: {
			entries: "src/**/*.{js,vue}",
			exclude: ["vitest", "vue"],
			holdUntilCrawlEnd: false
		},
		resolve: {
			alias: createAliases()
		},
		server: createServer(),
		test: createTest()
	};
});
