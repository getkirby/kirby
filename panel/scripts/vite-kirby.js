/* eslint-env node */
import fs from "fs";

export function dev() {
	return {
		name: "kirby-dev-mode",
		config(config, { command }) {
			// Tell Kirby that we are in dev mode
			if (command === "serve") {
				// Create the flag file on start
				const file = __dirname + "/../.vite-running";
				fs.closeSync(fs.openSync(file, "w"));

				// Delete the flag file on any kind of exit
				for (const event of ["exit", "SIGINT", "uncaughtException"]) {
					process.on(event, function (err) {
						if (fs.existsSync(file) === true) {
							fs.unlinkSync(file);
						}

						if (event === "uncaughtException") {
							console.error(err);
						}

						process.exit();
					});
				}
			}
		}
	};
}
