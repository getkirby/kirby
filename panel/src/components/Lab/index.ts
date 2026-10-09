import { defineAsyncComponent } from "vue";

/**
 * All Lab views resolve through this one dynamic import,
 * so that they share a single lazy chunk. Its file name
 * also becomes the chunk name: `Lab.min.js`
 */
function lab() {
	return import("./Lab");
}

const DocsView = defineAsyncComponent(async () => (await lab()).DocsView);
const IndexView = defineAsyncComponent(async () => (await lab()).IndexView);
const PlaygroundView = defineAsyncComponent(
	async () => (await lab()).PlaygroundView
);

export {
	DocsView as "k-lab-docs-view",
	IndexView as "k-lab-index-view",
	PlaygroundView as "k-lab-playground-view"
};
