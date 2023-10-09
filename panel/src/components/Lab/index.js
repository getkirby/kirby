const IndexView = () => import("./IndexView.vue");
const PlaygroundView = () => import("./PlaygroundView.vue");

export default {
	install(app) {
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-playground-view", PlaygroundView);
	}
};
