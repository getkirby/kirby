import Inside from "./Inside.vue";
import Menu from "./Menu.vue";
import Outside from "./Outside.vue";
import Panel from "./Panel.vue";

export default {
	install(app) {
		app.component("k-panel", Panel);
		app.component("k-panel-inside", Inside);
		app.component("k-panel-menu", Menu);
		app.component("k-panel-outside", Outside);

		// @deprecated Use `k-panel-inside` instead
		app.component("k-inside", Inside);

		// @deprecated Use `k-panel-outside` instead
		app.component("k-outside", Outside);
	}
};
