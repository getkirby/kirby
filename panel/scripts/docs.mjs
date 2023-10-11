import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath, pathToFileURL } from "url";
import docgen from "vue-docgen-api";

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

	// Parse each Vue SFC file and write earch result to a separate JSON file
	for (const file of files) {
		// parse with Vue docgen API
		const data = await docgen.parse(file, { alias });

		// clean up data
		delete data.sourceFiles;
		data.sourceFile = path.relative(root, file);

		// write file
		fs.writeFileSync(
			path.resolve(dist, data.displayName + ".json"),
			JSON.stringify(data)
		);
	}

	return files;
}

if (import.meta.url === pathToFileURL(process.argv[1]).href) {
	console.log("\n");
	console.log("Generating UI documentation...");

	// Pass absolute file path from -- argument, if given
	generate(process.argv[2]).then((files) => {
		console.log("--> " + files.length + " components compiled");
	});
}
