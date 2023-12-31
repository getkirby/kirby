import Toolbar from "./Toolbar.vue";

// @deprecated
import WriterInput from "../Input/WriterInput.vue";

export default {
	install(app) {
		app.component("k-writer-toolbar", Toolbar);

		// @deprecated use k-writer-input instead
		app.component("k-writer", WriterInput);
	}
};
