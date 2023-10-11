import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath } from "url";
import docgen from "vue-docgen-api";

console.log("\n");
console.log("Generating UI documentation...");

const script = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(script, "../");
const dist = path.resolve(root, "./dist/ui");
const alias = { "@": path.resolve(root, "./src/") };

if (fs.existsSync(dist) === false) {
	fs.mkdirSync(dist);
}

let files = [];

// Checks for -- file argument
if (process.argv[2]) {
	files = [path.resolve(root, "./" + process.argv[2])];
}

// If no --file argument is given, get all Vue SFC files
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

console.log("--> " + files.length + " components compiled");
