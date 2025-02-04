/* eslint-env node */
import path from "path";

import { defineConfig, loadEnv, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue2";
import { viteStaticCopy } from "vite-plugin-static-copy";
import externalize from "rollup-plugin-external-globals";
import kirby from "./scripts/vite-kirby.mjs";

/**
 * Returns all aliases used in the project
 */
function createAliases() {
	return {
		"@": path.resolve(__dirname, "src")
	};
}

/**
 * Returns the server configuration
 */
function createServer() {
	const proxy = {
		target: process.env.SERVER ?? "https://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	return {
		allowedHosts: [proxy.target.substring(8)],
		cors: { origin: proxy.target },
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
						src: "node_modules/vue/dist/vue.runtime.min.js",
						dest: "js"
					},
					{
						src: "node_modules/vue/dist/vue.min.js",
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
				...externalize({ vue: "Vue" }),
				enforce: "post"
			}
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
export default defineConfig(({ command, mode }) => {
	// Load env file based on `mode` in the current working directory.
	// Set the third parameter to '' to load all env regardless of the `VITE_` prefix.
	process.env = {
		...process.env,
		...loadEnv(mode, process.cwd(), "")
	};

	return {
		plugins: createPlugins(command),
		define: {
			// Fix vuelidate error
			"process.env.BUILD": JSON.stringify("production")
		},
		base: "./",
		build: {
			minify: "terser",
			cssCodeSplit: false,
			rollupOptions: {
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
