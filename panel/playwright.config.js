/* eslint-env node */
import { defineConfig } from "@playwright/test";

const BASE_URL = process.env.BASE_URL ?? "https://sandbox.test";

export default defineConfig({
	testMatch: ["{lab,e2e}/**/*.spec.js"],
	outputDir: "e2e/results",
	reporter: process.env.CI ? "dot" : [["html", { outputFolder: "e2e/report" }]],
	snapshotDir: "e2e/screenshots",
	snapshotPathTemplate: "{snapshotDir}/{testFilePath}/{arg}{ext}",

	use: {
		baseURL: BASE_URL,
		ignoreHTTPSErrors: true
	},

	projects: [
		{
			name: "setup",
			testMatch: "e2e/setup.js"
		},
		{
			name: "tests",
			dependencies: ["setup"],
			use: {
				storageState: "e2e/session.json",
				viewport: { width: 1280, height: 720 },
				launchOptions: {
					args: [
						"--font-render-hinting=none",
						"--disable-font-subpixel-positioning"
					]
				}
			}
		}
	]
});
