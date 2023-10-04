import Code from "./Code.vue";
import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";
import Example from "./Example.vue";
import Examples from "./Examples.vue";
import FieldExamples from "./FieldExamples.vue";
import FieldPreviewExample from "./FieldPreviewExample.vue";
import Form from "./Form.vue";
import Index from "./Index.vue";
import InputExamples from "./InputExamples.vue";
import InputboxExamples from "./InputboxExamples.vue";
import OptionsFieldExamples from "./OptionsFieldExamples.vue";
import OptionsInputExamples from "./OptionsInputExamples.vue";
import OptionsInputboxExamples from "./OptionsInputboxExamples.vue";
import OutputDialog from "./OutputDialog.vue";
import Playground from "./Playground.vue";

export default {
	install(app) {
		app.component("k-ui-code", Code);
		app.component("k-ui-docs", Docs);
		app.component("k-ui-docs-drawer", DocsDrawer);
		app.component("k-ui-example", Example);
		app.component("k-ui-examples", Examples);
		app.component("k-ui-field-examples", FieldExamples);
		app.component("k-ui-field-preview-example", FieldPreviewExample);
		app.component("k-ui-form", Form);
		app.component("k-ui-index-view", Index);
		app.component("k-ui-input-examples", InputExamples);
		app.component("k-ui-inputbox-examples", InputboxExamples);
		app.component("k-ui-options-field-examples", OptionsFieldExamples);
		app.component("k-ui-options-input-examples", OptionsInputExamples);
		app.component("k-ui-options-inputbox-examples", OptionsInputboxExamples);
		app.component("k-ui-output-dialog", OutputDialog);
		app.component("k-ui-playground-view", Playground);
	}
};
