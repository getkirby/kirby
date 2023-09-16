/* eslint-env node */
import fs from "fs";

export default () => ({
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
});
