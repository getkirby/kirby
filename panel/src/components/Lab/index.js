import { defineAsyncComponent } from "vue";

const IndexView = defineAsyncComponent(() => import("./IndexView.vue"));
const DocsView = defineAsyncComponent(() => import("./DocsView.vue"));
const PlaygroundView = defineAsyncComponent(
	() => import("./PlaygroundView.vue")
);

export default {
	install(app) {
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-docs-view", DocsView);
		app.component("k-lab-playground-view", PlaygroundView);
	}
};
