import Layout from "./Layout.vue";
import LayoutColumn from "./LayoutColumn.vue";
import Layouts from "./Layouts.vue";
import LayoutSelector from "./LayoutSelector.vue";

export default {
	install(app) {
		app.component("k-layout", Layout);
		app.component("k-layout-column", LayoutColumn);
		app.component("k-layouts", Layouts);
		app.component("k-layout-selector", LayoutSelector);
	}
};
