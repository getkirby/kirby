import Elements from "./Elements/index.js";

import Drawer from "./Drawer.vue";

import BlockDrawer from "./BlockDrawer.vue";
import StateDrawer from "./StateDrawer.vue";
import FormDrawer from "./FormDrawer.vue";
import StructureDrawer from "./StructureDrawer.vue";
import TextDrawer from "./TextDrawer.vue";
import UserSecurityDrawer from "./UserSecurityDrawer.vue";

export default {
	install(app) {
		app.use(Elements);

		app.component("k-drawer", Drawer);

		app.component("k-block-drawer", BlockDrawer);
		app.component("k-state-drawer", StateDrawer);
		app.component("k-form-drawer", FormDrawer);
		app.component("k-structure-drawer", StructureDrawer);
		app.component("k-text-drawer", TextDrawer);
		app.component("k-user-security-drawer", UserSecurityDrawer);
	}
};
