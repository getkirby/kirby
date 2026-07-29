import { defineAsyncComponent } from "vue";

/**
 * All Lab views resolve through this one dynamic import,
 * so that they share a single lazy chunk. Its file name
 * also becomes the chunk name: `Lab.min.js`
 */
function lab() {
	return import("./Lab.js");
}

const DocsView = defineAsyncComponent(async () => (await lab()).DocsView);
const IndexView = defineAsyncComponent(async () => (await lab()).IndexView);
const PlaygroundView = defineAsyncComponent(
	async () => (await lab()).PlaygroundView
);

export default {
	install(app) {
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-docs-view", DocsView);
		app.component("k-lab-playground-view", PlaygroundView);
	}
};
