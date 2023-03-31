import Body from "./Body.vue";
import Box from "./Box.vue";
import Notification from "./Notification.vue";
import Tabs from "./Tabs.vue";

export default {
	install(app) {
		app.component("k-drawer-body", Body);
		app.component("k-drawer-box", Box);
		app.component("k-drawer-notification", Notification);
		app.component("k-drawer-tabs", Tabs);
	}
};
