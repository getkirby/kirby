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
 * Generate tmp UI docs file on change
 * and send reload events to client for docs
 * and lab example changes.
 */
function labDev() {
	return {
		name: "kirby-lab-dev",
		apply: "serve",
		configureServer({ watcher, ws }) {
			watcher.on("change", async (file) => {
				// Vue components: regenerate docs and send reload to client
				if (/panel\/src\/.*\.vue/.test(file) === true) {
					const doc = await generateDocs(file);
					ws.send("kirby:docs:" + doc?.component);
				}

				// Lab examples: send reload to client
				const examples = file.match(/panel\/lab\/(.*)\/index(.vue|.php)/);
				if (examples !== null) {
					ws.send("kirby:example:" + examples[1]);
				}
			});
		}
	};
}

/**
 * Generate all UI docs on build
 */
function labBuild() {
	return {
		name: "kirby-lab-build",
		apply: "build",
		async writeBundle() {
			const docs = await generateDocs();
			console.log(`\x1b[32m✓\x1b[0m ${docs.length} UI docs generated.`);
		}
	};
}

function removeDocsBlock() {
	return {
		name: "kirby-remove-docs-block",
		transform(code, id) {
			if (/vue&type=docs/.test(id) === false) {
				return;
			}

			return `export default ''`;
		}
	};
}

export default function kirby() {
	return [devMode(), removeDocsBlock(), labDev(), labBuild()];
}
