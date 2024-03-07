import IndexView from "./IndexView.vue";
import DocsView from "./DocsView.vue";
import PlaygroundView from "./PlaygroundView.vue";

import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";
import Example from "./Example.vue";
import Examples from "./Examples.vue";
import Form from "./Form.vue";
import OutputDialog from "./OutputDialog.vue";
import TableCell from "./TableCell.vue";

import DocDeprecated from "./DocsDeprecated.vue";
import DocParams from "./DocsParams.vue";
import DocTypes from "./DocsTypes.vue";

export default {
	install(app) {
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-docs-view", DocsView);
		app.component("k-lab-playground-view", PlaygroundView);

		app.component("k-lab-docs", Docs);
		app.component("k-lab-docs-drawer", DocsDrawer);
		app.component("k-lab-example", Example);
		app.component("k-lab-examples", Examples);
		app.component("k-lab-form", Form);
		app.component("k-lab-output-dialog", OutputDialog);
		app.component("k-lab-table-cell", TableCell);

		app.component("k-lab-docs-deprecated", DocDeprecated);
		app.component("k-lab-docs-params", DocParams);
		app.component("k-lab-docs-types", DocTypes);
	}
};
