import Inside from "./Inside.vue";
import Outside from "./Outside.vue";
import Panel from "./Panel.vue";

export default {
	install(app) {
		app.component("k-inside", Inside);
		app.component("k-outside", Outside);
		app.component("k-panel", Panel);
	}
};
