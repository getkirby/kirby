/* Form */
import Autocomplete from "./Autocomplete.vue";
import Counter from "./Counter.vue";
import Form from "./Form.vue";
import FormButtons from "./FormButtons.vue";
import Field from "./Field.vue";
import Fieldset from "./Fieldset.vue";
import Input from "./Input.vue";
import Login from "./Login.vue";
import LoginCode from "./LoginCode.vue";
import Selector from "./Selector.vue";
import Upload from "./Upload.vue";

/** Form Helpers */
import LoginAlert from "./LoginAlert.vue";

/* Form Structure */
import StructureForm from "./Structure/StructureForm.vue";

/* Form parts */
import Blocks from "./Blocks/index.js";
import Fields from "./Field/index.js";
import Inputs from "./Input/index.js";
import Layouts from "./Layouts/index.js";
import Previews from "./Previews/index.js";
import Toolbar from "./Toolbar/index.js";
import Writer from "./Writer/index.js";

export default {
	install(app) {
		app.component("k-counter", Counter);
		app.component("k-autocomplete", Autocomplete);
		app.component("k-form", Form);
		app.component("k-form-buttons", FormButtons);
		app.component("k-field", Field);
		app.component("k-fieldset", Fieldset);
		app.component("k-input", Input);
		app.component("k-login", Login);
		app.component("k-login-code", LoginCode);
		app.component("k-selector", Selector);
		app.component("k-upload", Upload);

		app.component("k-login-alert", LoginAlert);

		app.component("k-structure-form", StructureForm);

		app.use(Blocks);
		app.use(Inputs);
		app.use(Fields);
		app.use(Layouts);
		app.use(Previews);
		app.use(Toolbar);
		app.use(Writer);
	}
};
