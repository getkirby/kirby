import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath, pathToFileURL } from "url";
import docgen from "vue-docgen-api";

/**
 * Normalize doc json and strip unnecessary data
 *
 * @param {Object} data
 * @param {String} path
 * @returns {Object}
 */
export function normalizeDoc(data, path) {
	// add additional data
	data.component =
		"k-" +
		data.displayName.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
	data.sourceFile = path;

	// strip unnecessary data
	delete data.exportName;

	for (const access in data.tags.access ?? []) {
		delete data.tags.access[access].title;
	}

	for (const type of ["props", "slots", "events", "methods"]) {
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
	const alias = { "@": path.resolve(root, "./src/") };

	let dist;

	if (file) {
		dist = path.resolve(root, "./tmp");
	} else {
		dist = path.resolve(root, "./dist/ui");
	}

	if (fs.existsSync(dist) === false) {
		fs.mkdirSync(dist);
	}

	// If file argument is not given, get all Vue SFC files
	const files = file ? [file] : await glob("src/components/**/*.vue");
	const docs = [];

	// Parse each Vue SFC file and write earch result to a separate JSON file
	for (const file of files) {
		// skip Lab files
		if (/src\/components\/Lab\//.test(file) === true) {
			continue;
		}

		// parse with Vue docgen API
		try {
			let doc = await docgen.parse(file, { alias });

			if (doc.tags.internal?.[0]?.description !== true) {
				doc = normalizeDoc(doc, path.relative(root, file));

				// write file
				fs.writeFileSync(
					path.resolve(dist, doc.displayName + ".json"),
					JSON.stringify(doc)
				);

				docs.push(doc);
			}
		} catch (error) {
			console.error(error);
		}
	}

	return docs;
}

// If this file is run from CLI
if (import.meta.url === pathToFileURL(process.argv[1]).href) {
	console.log("Generating UI documentation…");
	// Pass absolute file path from -- argument, if given
	generate(process.argv[2]).then((docs) => {
		console.log(`\x1b[32m✓\x1b[0m ${docs.length} UI docs generated.`);
	});
}
