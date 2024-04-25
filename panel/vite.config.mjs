/* eslint-env node */
import path from "path";

import { defineConfig, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue";
import { viteStaticCopy } from "vite-plugin-static-copy";
import externalGlobals from "rollup-plugin-external-globals";
import kirby from "./scripts/vite-kirby.mjs";

let customServer;
try {
	customServer = require("./vite.config.custom.js");
} catch (err) {
	customServer = {};
}

export default defineConfig(({ command }) => {
	// gather plugins depending on environment
	const plugins = [
		vue({
			template: {
				compilerOptions: {
					isCustomElement: (tag) => ["k-input-validator"].includes(tag),
					compatConfig: {
						MODE: 2,
						COMPILER_V_BIND_OBJECT_ORDER: false
					}
				}
			}
		}),
		splitVendorChunkPlugin(),
		kirby()
	];

	if (command === "build") {
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

	const proxy = {
		target: process.env.VUE_APP_DEV_SERVER ?? "http://sandbox.test",
		changeOrigin: true,
		secure: false
	};

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
			alias: {
				"@": path.resolve(__dirname, "src"),
				vue: "vue/dist/vue.esm-browser.js"
			}
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
			environment: "node",
			include: ["**/*.test.js"],
			setupFiles: ["vitest.setup.js"]
		}
	};
});
