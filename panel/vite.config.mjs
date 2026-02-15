/* eslint-env node */
import path from "path";

import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import kirby from "./scripts/vite-kirby.mjs";

/**
 * Returns all aliases used in the project
 */
function createAliases(proxy) {
	const aliases = {
		"@": path.resolve(__dirname, "src")
	};

	if (!process.env.VITEST) {
		// use absolute proxied url to avoid Vue being loaded twice
		aliases.vue =
			proxy.target + ":3000/node_modules/vue/dist/vue.esm-browser.js";
	}

	return aliases;
}

/**
 * Returns the server configuration
 */
async function createServer(proxy) {
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
		...(await createCustomServer())
	};
}

/**
 * Returns custom server configuration, if it exists
 */
async function createCustomServer() {
	try {
		const module = await import("./vite.config.custom.js");
		return module.default ?? {};
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
export default defineConfig(async ({ mode }) => {
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

	const alias = createAliases(proxy);
	const plugins = createPlugins(mode);
	const server = await createServer(proxy);
	const test = createTest();

	return {
		plugins,
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
					assetFileNames: "[ext]/[name].min.[ext]",
					manualChunks(id) {
						if (id.includes("sortablejs")) {
							return "sortable";
						}

						if (id.includes("node_modules")) {
							return "vendor";
						}

						return null;
					}
				}
			}
		},
		optimizeDeps: {
			entries: "src/**/*.{js,vue}",
			exclude: ["vitest", "vue"],
			holdUntilCrawlEnd: false
		},
		resolve: {
			alias
		},
		server,
		test
	};
});
