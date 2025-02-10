/* eslint-env node */
import path from "path";

import { defineConfig, loadEnv, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import kirby from "./scripts/vite-kirby.mjs";
import postcssLightDarkFunction from "@csstools/postcss-light-dark-function";

/**
 * Returns all aliases used in the project
 */
function createAliases(proxy) {
	return {
		"@": path.resolve(__dirname, "src"),
		// use absolute proxied url to avoid Vue being loaded twice
		vue: proxy.target + ":3000/node_modules/vue/dist/vue.esm-browser.js"
	};
}

/**
 * Returns the server configuration
 */
function createServer(proxy) {
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
	const plugins = [
		vue({
			template: {
				compilerOptions: {
					isCustomElement: (tag) => ["k-input-validator"].includes(tag)
				}
			}
		}),
		splitVendorChunkPlugin(),
		kirby()
	];

	// when building…
	if (mode === "production") {
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
					},
					{
						src: "node_modules/vue/dist/vue.runtime.esm-browser.prod.js",
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
export default defineConfig(({ mode }) => {
	// Load env file based on `mode` in the current working directory.
	// Set the third parameter to '' to load all env regardless of the `VITE_` prefix.
	process.env = {
		...process.env,
		...loadEnv(mode, process.cwd(), "")
	};

	const proxy = {
		target: process.env.SERVER ?? "https://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	return {
		plugins: createPlugins(mode),
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
		css: {
			postcss: {
				plugins: [postcssLightDarkFunction()]
			}
		},
		optimizeDeps: {
			entries: "src/**/*.{js,vue}",
			exclude: ["vitest", "vue"],
			holdUntilCrawlEnd: false
		},
		resolve: {
			alias: createAliases(proxy)
		},
		server: createServer(proxy),
		test: createTest()
	};
});
