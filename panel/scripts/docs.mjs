import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath, pathToFileURL } from "url";
import docgen from "vue-docgen-api";

/**
 * Strips unnecessary data from the
 * Vue component docs' JSON object
 *
 * @param {Object} data
 * @returns {Object}
 */
export function strip(data) {
	delete data.exportName;

	for (const access in data.tags.access ?? []) {
		delete data.tags.access[access].title;
	}

	for (const type of ["props", "slots", "events"]) {
		for (const key in data[type] ?? {}) {
			delete data[type][key].mixin;
			delete data[type][key].defaultValue?.func;
			delete data[type][key].tags?.func;

			for (const access in data[type][key].tags?.access ?? []) {
				delete data[type][key].tags?.access[access].title;
			}
		}
	}

	delete data.sourceFiles;

	return data;
}

/**
 * Generates JSON files for the Vue component
 * passed to the function, or all Vue components
 * if no argument is given.
 *
 * @param {String} file
 * @returns {Array}
 */
export default async function generate(file) {
	const script = path.dirname(fileURLToPath(import.meta.url));
	const root = path.resolve(script, "../");
	const dist = path.resolve(root, "./dist/ui");
	const alias = { "@": path.resolve(root, "./src/") };

	if (fs.existsSync(dist) === false) {
		fs.mkdirSync(dist);
	}

	let files = [];

	if (file) {
		files = [file];
	}

	// If file argument is given, get all Vue SFC files
	if (files.length === 0) {
		files = await glob("src/components/**/*.vue");
	}

	const components = [];

	// Parse each Vue SFC file and write earch result to a separate JSON file
	for (const file of files) {
		// parse with Vue docgen API
		const data = strip(await docgen.parse(file, { alias }));
		data.sourceFile = path.relative(root, file);

		// write file
		fs.writeFileSync(
			path.resolve(dist, data.displayName + ".json"),
			JSON.stringify(data)
		);

		components.push(data);
	}

	return components;
}

// If this file is run from CLI
if (import.meta.url === pathToFileURL(process.argv[1]).href) {
	console.log("\n");
	console.log("Generating UI documentation...");

	// Pass absolute file path from -- argument, if given
	generate(process.argv[2]).then((components) => {
		console.log("--> " + components.length + " components compiled");
	});
}
