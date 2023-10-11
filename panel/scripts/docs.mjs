import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath } from "url";
import docgen from "vue-docgen-api";

console.log("\n");
console.log("Generating UI documentation...");

const root = path.dirname(fileURLToPath(import.meta.url));
const alias = { "@": path.resolve(root, "../src/") };

// Get all Vue SFC files
const files = await glob("src/components/**/*.vue");

// Parse all components at once with Promise.all()
const components = await Promise.all(
	files.map(async (file) => {
		let data = await docgen.parse(file, { alias });
		data.srcFile = file;
		return data;
	})
);

// Write to JSON file
fs.writeFileSync(
	path.resolve(root, "../dist/ui.json"),
	JSON.stringify(components)
);

console.log("-> " + components.length + " components compiled");
