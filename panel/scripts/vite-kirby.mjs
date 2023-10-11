/* eslint-env node */
import fs from "fs";
import generateUi from "./docs.mjs";

export function devMode() {
	return {
		name: "kirby-dev-mode",
		config(config, { command }) {
			// Tell Kirby that we are in dev mode
			if (command === "serve") {
				// Create the flag file on start
				const runningPath = __dirname + "/../.vite-running";
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
		}
	};
}

export function generateUiDocs(config) {
	return {
		name: "kirby-generate-ui-docs",
		configureServer(server) {
			server.watcher.on("change", (file) => {
				if (file.endsWith(".vue") === true) {
					generateUi(file);
				}
			});
		}
	};
}
