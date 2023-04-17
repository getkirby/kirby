/* eslint-env node */
import fs from "fs";
import path from "path";
import { defineConfig, splitVendorChunkPlugin } from "vite";
import vue from "@vitejs/plugin-vue2";
import externalGlobals from "rollup-plugin-external-globals";
import { viteStaticCopy } from "vite-plugin-static-copy";
import postcssAutoprefixer from "autoprefixer";
import postcssCsso from "postcss-csso";
import postcssDirPseudoClass from "postcss-dir-pseudo-class";
import postcssLogical from "postcss-logical";

let custom;
try {
	custom = require("./vite.config.custom.js");
} catch (err) {
	custom = {};
}

export default defineConfig(({ command }) => {
	// Tell Kirby that we are in dev mode
	if (command === "serve") {
		// Create the flag file on start
		const runningPath = __dirname + "/.vite-running";
		fs.closeSync(fs.openSync(runningPath, "w"));

		// Delete the flag file on any kind of exit
		for (const eventType of ["exit", "SIGINT", "uncaughtException"]) {
			process.on(eventType, function (err) {
				if (fs.existsSync(runningPath) === true) {
					fs.unlinkSync(runningPath);
				}

				if (eventType === "uncaughtException") {
					console.error(err);
				}

				process.exit();
			});
		}
	}

	const proxy = {
		target: process.env.VUE_APP_DEV_SERVER || "http://sandbox.test",
		changeOrigin: true,
		secure: false
	};

	const plugins = [vue(), splitVendorChunkPlugin()];

	if (!process.env.VITEST) {
		plugins.push(
			// Externalize Vue so it's not loaded from node_modules but accessed via window.Vue
			{
				...externalGlobals({ vue: "Vue" }),
				enforce: "post"
			}
		);
	}

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
			port: 3000,
			...custom
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
