import fs from "fs";
import { glob } from "glob";
import path from "path";
import { fileURLToPath } from "url";
import docgen from "vue-docgen-api";

console.log("\n");
console.log("Generating UI documentation...");

const root = path.dirname(fileURLToPath(import.meta.url));
const dist = path.resolve(root, "../dist/ui");
const alias = { "@": path.resolve(root, "../src/") };

if (fs.existsSync(dist) === false) {
	fs.mkdirSync(dist);
}

// Get all Vue SFC files
const files = await glob("src/components/**/*.vue");

for (const file of files) {
	const data = await docgen.parse(file, { alias });
	fs.writeFileSync(
		path.resolve(dist, data.displayName + ".json"),
		JSON.stringify(data)
	);
}

console.log("-> " + files.length + " components compiled");
