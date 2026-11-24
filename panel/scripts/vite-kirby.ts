/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */

import fs from "fs";
import generateDocs from "./docs.ts";
import { type ConfigEnv, type Plugin, type UserConfig } from "vite";

/**
 * Applies a plugin to the dev server, but not to Vitest
 */
function serve(config: UserConfig, env: ConfigEnv): boolean {
	return env.command === "serve" && process.env.VITEST === undefined;
}

/**
 * Creates flag file to tell Kirby that we are in dev mode
 */
function devMode(): Plugin {
	const flag = import.meta.dirname + "/../.vite-running";
	const tmp = import.meta.dirname + "/../tmp";

	function clean(): void {
		fs.rmSync(flag, { force: true });
		fs.rmSync(tmp, { recursive: true, force: true });
	}

	function exit(): void {
		process.exit();
	}

	return {
		name: "kirby-dev-mode",
		apply: serve,
		configureServer({ httpServer }) {
			httpServer?.once("listening", () => {
				fs.writeFileSync(flag, "");
				process.on("exit", clean);
				process.on("SIGHUP", exit);
				process.on("SIGINT", exit);
			});

			// Vite closes the server on SIGTERM and on restarts
			httpServer?.once("close", () => {
				clean();
				process.off("exit", clean);
				process.off("SIGHUP", exit);
				process.off("SIGINT", exit);
			});
		}
	};
}

/**
 * Generate tmp UI docs file on change
 * and send reload events to client for docs
 * and lab example changes.
 */
function labDev(): Plugin {
	return {
		name: "kirby-lab-dev",
		apply: serve,
		configureServer({ watcher, ws }) {
			watcher.on("change", async (file) => {
				// Vue components: regenerate docs in tmp directory
				// and send reload to client
				if (/panel\/src\/.*\.vue/.test(file) === true) {
					const docs = await generateDocs(file);
					ws.send("kirby:docs:" + docs[0]?.component);
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
function labBuild(): Plugin {
	return {
		name: "kirby-lab-build",
		apply: "build",
		async writeBundle() {
			process.stdout.write("  Generating UI docs...");
			const docs = await generateDocs();
			process.stdout.write(
				`\r\x1b[32m✓\x1b[0m ${docs.length} UI docs generated.   \n`
			);
		}
	};
}

function removeDocsBlock(): Plugin {
	return {
		name: "kirby-remove-docs-block",
		transform: {
			filter: { id: /vue&type=docs/ },
			handler() {
				return { code: `export default ''`, moduleType: "js" };
			}
		}
	};
}

export default function kirby(): Plugin[] {
	return [devMode(), removeDocsBlock(), labDev(), labBuild()];
}
