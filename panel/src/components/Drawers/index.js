import DrawerTabs from "@/components/Drawers/DrawerTabs.vue";

import Drawer from "@/components/Drawers/Drawer.vue";
import FormDrawer from "@/components/Drawers/FormDrawer.vue";

export default {
	install(app) {
		app.component("k-drawer-tabs", DrawerTabs);

		app.component("k-drawer", Drawer);
		app.component("k-form-drawer", FormDrawer);
	}
};
