/* eslint-env node */
import path from "path";

import { defineConfig, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import kirby from "./scripts/vite-kirby.mjs";

/**
 * Returns all aliases used in the project
 */
function createAliases(proxy) {
	return {
		"@": path.resolve(__dirname, "src"),
		vue: proxy.target + ":3000/node_modules/vue/dist/vue.esm-browser.js"
	};
}

function createProxy() {
	return {
		target: process.env.VUE_APP_DEV_SERVER ?? "https://sandbox.test",
		changeOrigin: true,
		secure: false
	};
}

/**
 * Returns the server configuration
 */
function createServer(proxy) {
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
	const proxy = createProxy();

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
