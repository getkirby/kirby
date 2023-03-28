import LinkDialog from "./Dialogs/LinkDialog.vue";
import EmailDialog from "./Dialogs/EmailDialog.vue";

import Toolbar from "./Toolbar.vue";

import Writer from "./Writer.vue";

export default {
	install(app) {
		app.component("k-writer-email-dialog", EmailDialog);
		app.component("k-writer-link-dialog", LinkDialog);

		app.component("k-writer-toolbar", Toolbar);

		app.component("k-writer", Writer);
	}
};
