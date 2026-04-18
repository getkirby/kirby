/* eslint-env node */
import path from "path";

import {
	type AliasOptions,
	defineConfig,
	loadEnv,
	type Plugin,
	ProxyOptions,
	type ServerOptions
} from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import kirby from "./scripts/vite-kirby";

type ProxyConfig = ProxyOptions & { target: string };

/**
 * Get custom server config, if present
 */
let customServer = {};

try {
	const module = await import("./vite.config.custom.js");
	customServer = module.default ?? {};
} catch {}

/**
 * Returns all aliases used in the project
 */
function createAliases(proxy: ProxyConfig): AliasOptions {
	const aliases: Record<string, string> = {
		"@": path.resolve(__dirname, "src")
	};

	if (process.env.VITEST) {
		aliases["@test"] = path.resolve(__dirname, "tests");
	} else {
		// use absolute proxied url to avoid Vue being loaded twice
		aliases.vue =
			proxy.target + ":3000/node_modules/vue/dist/vue.esm-browser.js";
	}

	return aliases;
}

/**
 * Returns the server configuration
 */
function createServer(proxy: ProxyConfig): ServerOptions {
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
		...(customServer ?? {})
	};
}

/**
 * Returns an array of plugins used,
 * depending on the mode (development or build)
 */
function createPlugins(mode: string): Plugin[] {
	const plugins: Plugin[] = [
		vue({
			template: {
				compilerOptions: {
					isCustomElement: (tag) =>
						["k-input-validator"].includes(tag) ||
						(!!process.env.VITEST && tag.startsWith("k-"))
				}
			}
		}),
		...kirby()
	];

	// when building…
	if (mode === "production") {
		//copy Vue to the dist directory
		plugins.push(
			...viteStaticCopy({
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
		css: false,
		environment: "happy-dom",
		include: ["**/*.test.{js,ts}"],
		reporter: "dot",
		setupFiles: ["tests/unit/setup.ts"]
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

	const proxy: ProxyConfig = {
		target: process.env.SERVER ?? "https://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	const alias = createAliases(proxy);
	const plugins = createPlugins(mode);
	const server = createServer(proxy);
	const test = createTest();

	return {
		plugins,
		base: "./",
		build: {
			target: ["chrome123", "edge123", "firefox120", "safari17.5", "ios17.5"],
			cssCodeSplit: false,
			rolldownOptions: {
				checks: { pluginTimings: false },
				external: ["vue"],
				input: "./src/index.js",
				output: {
					entryFileNames: "js/[name].min.js",
					chunkFileNames: "js/[name].min.js",
					assetFileNames: "[ext]/[name].min.[ext]",
					codeSplitting: {
						groups: [
							{
								name: "vendor",
								test: /node_modules\/(?!sortablejs\/)|plugin-vue:export-helper|vite\/preload-helper|rolldown:runtime/
							}
						]
					}
				}
			}
		},
		optimizeDeps: {
			entries: ["src/**/*.{js,ts,vue}", "!src/**/*.test.{js,ts}"],
			exclude: ["vitest", "vue"]
		},
		resolve: {
			alias
		},
		server,
		test
	};
});
