import Toolbar from "./Toolbar.vue";
import Writer from "./Writer.vue";

export default {
	install(app) {
		app.component("k-writer-toolbar", Toolbar);
		app.component("k-writer", Writer);
	}
};
