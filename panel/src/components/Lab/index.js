const IndexView = () => import("./IndexView.vue");
const DocsView = () => import("./DocsView.vue");
const PlaygroundView = () => import("./PlaygroundView.vue");

export default {
	install(app) {
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-docs-view", DocsView);
		app.component("k-lab-playground-view", PlaygroundView);
	}
};
