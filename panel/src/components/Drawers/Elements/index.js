import Body from "./Body.vue";
import Tabs from "./Tabs.vue";

export default {
	install(app) {
		app.component("k-drawer-body", Body);
		app.component("k-drawer-tabs", Tabs);
	}
};
