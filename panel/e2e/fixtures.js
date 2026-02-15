import { readFileSync } from "fs";
import { expect, test as base } from "@playwright/test";

// Inject Inter font for better rendering of screenshots on Linux
function fontFace(weight) {
	const file = new URL(
		`../node_modules/@fontsource/inter/files/inter-latin-${weight}-normal.woff2`,
		import.meta.url
	);
	const data = readFileSync(file).toString("base64");
	return `@font-face {
		font-family: "Inter";
		font-style: normal;
		font-weight: ${weight};
		font-display: block;
		src: url("data:font/woff2;base64,${data}") format("woff2");
	}`;
}

const interCSS = [300, 400, 500, 600].map(fontFace).join("\n");

// Make all headers non-sticky for better screenshots
const resetCSS = `
.k-header {
	position: static !important;
}`;

export const test = base.extend({
	context: async ({ context }, use) => {
		await context.addInitScript((css) => {
			document.addEventListener("DOMContentLoaded", () => {
				const style = document.createElement("style");
				style.textContent = css;
				document.head.appendChild(style);
			});
		}, [interCSS, resetCSS].join("\n"));
		await use(context);
	}
});

export { expect };
