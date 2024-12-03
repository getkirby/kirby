/* Form */
import Counter from "./Counter.vue";
import Field from "./Field.vue";
import Fieldset from "./Fieldset.vue";
import Form from "./Form.vue";
import FormControls from "./FormControls.vue";
import Input from "./Input.vue";

/* Form parts */
import Blocks from "./Blocks/index.js";
import Fields from "./Field/index.js";
import Inputs from "./Input/index.js";
import Layouts from "./Layouts/index.js";
import Previews from "./Previews/index.js";
import Toolbar from "./Toolbar/index.js";

export default {
	install(app) {
		app.component("k-counter", Counter);
		app.component("k-field", Field);
		app.component("k-fieldset", Fieldset);
		app.component("k-form", Form);
		app.component("k-form-controls", FormControls);
		app.component("k-input", Input);

		app.use(Blocks);
		app.use(Inputs);
		app.use(Fields);
		app.use(Layouts);
		app.use(Previews);
		app.use(Toolbar);
	}
};
