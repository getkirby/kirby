import Activation from "./Activation.vue";
import Buttons from "./Buttons/index.js";
import Inside from "./Inside.vue";
import Menu from "./Menu.vue";
import Outside from "./Outside.vue";
import Panel from "./Panel.vue";
import Topbar from "./Topbar.vue";

export default {
	install(app) {
		app.use(Buttons);

		app.component("k-activation", Activation);
		app.component("k-panel", Panel);
		app.component("k-panel-inside", Inside);
		app.component("k-panel-menu", Menu);
		app.component("k-panel-outside", Outside);
		app.component("k-topbar", Topbar);
	}
};
