/* eslint-env node */
import fs from "fs";
import generateDocs from "./docs.mjs";

/**
 * Creates flag file to tell Kirby that we are in dev mode
 */
function devMode() {
	return {
		name: "kirby-dev-mode",
		apply: "serve",
		config() {
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
	};
}

/**
 * Watch all Vue SFCs inside `panel/src`
 * and generate UI docs on change
 */
function labWatcher() {
	return {
		name: "kirby-lab-watcher",
		apply: "serve",
		configureServer({ watcher, ws }) {
			watcher.on("change", async (file) => {
				// Vue components: regenerate docs and send reload to client
				if (file.match(/panel\/src\/.*\.vue/) !== null) {
					const docs = await generateDocs(file);
					ws.send("kirby:docs:" + docs[0]?.component);
				}

				// Lab examples: send reload to client
				const examples = file.match(/panel\/lab\/(.*)\/index(.vue|.php)/);
				if (examples !== null) {
					const example = examples[1].replace(/\/[0-9]+_/, "_");
					ws.send("kirby:example:" + example);
				}
			});
		}
	};
}

export default function kirby() {
	return [devMode(), labWatcher()];
}
