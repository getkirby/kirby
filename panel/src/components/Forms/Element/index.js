import Choice from "./Choice.vue";
import Range from "./Range.vue";

export default {
	install(app) {
		app.component("k-choice", Choice);
		app.component("k-range", Range);
	}
};
