/* eslint-env node */
import fs from "fs";
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
						["k-input-validator", "k-validator"].includes(tag)
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
						dest: "js",
						rename: { stripBase: true }
					},
					{
						src: "node_modules/vue/dist/vue.esm-browser.prod.js",
						dest: "js",
						rename: { stripBase: true }
					}
				]
			})
		);
	}

	return plugins;
}

/**
 * Returns the build target, based on `.browserslistrc`
 */
function createTarget(): string[] {
	const engines: Record<string, string> = {
		Chrome: "chrome",
		Edge: "edge",
		Firefox: "firefox",
		iOS: "ios",
		Opera: "opera",
		Safari: "safari"
	};

	const file = fs.readFileSync(
		path.resolve(__dirname, ".browserslistrc"),
		"utf-8"
	);

	const target: string[] = [];

	for (const raw of file.split("\n")) {
		// strip comments, browserslist allows them at the end of a line
		const line = raw.replace(/#.*$/, "").trim();
		const match = line.match(/^(\w+)\s*>=\s*([\d.]+)$/);

		if (match !== null && engines[match[1]] !== undefined) {
			target.push(engines[match[1]] + match[2]);
		}
	}

	if (target.length === 0) {
		throw new Error("No build target could be read from .browserslistrc");
	}

	return target;
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
		setupFiles: ["tests/unit/setup.ts"],
		coverage: {
			provider: "v8",
			include: ["src/**/*.{js,ts,vue}"],
			exclude: ["src/**/*.test.{js,ts}", "src/**/index.{js,ts}"],
			// local: html report for browsing; CI: lcov for the Codecov upload
			reporter: process.env.CI ? ["lcov"] : ["text", "html"],
			reportsDirectory: "./tests/coverage"
		}
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
			target: createTarget(),
			cssCodeSplit: false,
			rolldownOptions: {
				checks: { pluginTimings: false },
				external: ["vue"],
				input: "./src/index.ts",
				output: {
					entryFileNames: "js/[name].min.js",
					chunkFileNames: "js/[name].min.js",
					assetFileNames: "[ext]/[name].min.[ext]",
					codeSplitting: {
						groups: [
							{
								name: "Sortable",
								test: /node_modules\/sortablejs\//
							},
							{
								name: "ProsemirrorModel",
								test: /node_modules\/prosemirror-model\//
							},
							{
								name: "Prosemirror",
								test: /node_modules\/prosemirror-/
							},
							{
								name: "vendor",
								test: /node_modules\/(?!sortablejs\/|prosemirror-)|plugin-vue:export-helper|vite\/preload-helper/
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
