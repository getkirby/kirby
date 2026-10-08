/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */

import fs from "fs";
import path from "path";
import { type Plugin } from "vite";

/**
 * Copies the files (relative to the project root)
 * into the given directory of the build output
 */
export default function copy(files: string[], dir: string): Plugin {
	let root: string;

	return {
		name: "kirby-copy",
		apply: "build",
		configResolved(config) {
			root = config.root;
		},
		generateBundle() {
			for (const file of files) {
				this.emitFile({
					type: "asset",
					fileName: dir + "/" + path.basename(file),
					source: fs.readFileSync(path.resolve(root, file))
				});
			}
		}
	};
}
