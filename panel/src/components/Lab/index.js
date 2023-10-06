import Code from "./Code.vue";
import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";
import Example from "./Example.vue";
import Examples from "./Examples.vue";
import FieldExamples from "./FieldExamples.vue";
import FieldPreviewExample from "./FieldPreviewExample.vue";
import Form from "./Form.vue";
import IndexView from "./IndexView.vue";
import InputExamples from "./InputExamples.vue";
import InputboxExamples from "./InputboxExamples.vue";
import OptionsFieldExamples from "./OptionsFieldExamples.vue";
import OptionsInputExamples from "./OptionsInputExamples.vue";
import OptionsInputboxExamples from "./OptionsInputboxExamples.vue";
import OutputDialog from "./OutputDialog.vue";
import PlaygroundView from "./PlaygroundView.vue";

export default {
	install(app) {
		app.component("k-lab-code", Code);
		app.component("k-lab-docs", Docs);
		app.component("k-lab-docs-drawer", DocsDrawer);
		app.component("k-lab-example", Example);
		app.component("k-lab-examples", Examples);
		app.component("k-lab-field-examples", FieldExamples);
		app.component("k-lab-field-preview-example", FieldPreviewExample);
		app.component("k-lab-form", Form);
		app.component("k-lab-index-view", IndexView);
		app.component("k-lab-input-examples", InputExamples);
		app.component("k-lab-inputbox-examples", InputboxExamples);
		app.component("k-lab-options-field-examples", OptionsFieldExamples);
		app.component("k-lab-options-input-examples", OptionsInputExamples);
		app.component("k-lab-options-inputbox-examples", OptionsInputboxExamples);
		app.component("k-lab-output-dialog", OutputDialog);
		app.component("k-lab-playground-view", PlaygroundView);
	}
};
