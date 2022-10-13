import Vue from "vue";

/* Form */
import Autocomplete from "./Autocomplete.vue";
import Calendar from "./Calendar.vue";
import Counter from "./Counter.vue";
import Form from "./Form.vue";
import FormButtons from "./FormButtons.vue";
import FormIndicator from "./FormIndicator.vue";
import Field from "./Field.vue";
import Fieldset from "./Fieldset.vue";
import Input from "./Input.vue";
import Login from "./Login.vue";
import LoginCode from "./LoginCode.vue";
import Times from "./Times.vue";
import Upload from "./Upload.vue";
import Writer from "./Writer/Writer.vue";

/** Form Helpers */
import LoginAlert from "./LoginAlert.vue";

/* Form Structure */
import StructureForm from "./Structure/StructureForm.vue";

/* Form Toolbar */
import Toolbar from "./Toolbar.vue";
import ToolbarEmailDialog from "./Toolbar/EmailDialog.vue";
import ToolbarLinkDialog from "./Toolbar/LinkDialog.vue";

/* Form Inputs */
import "./Input/index.js";

/* Form Fields */
import "./Field/index.js";

Vue.component("k-calendar", Calendar);
Vue.component("k-counter", Counter);
Vue.component("k-autocomplete", Autocomplete);
Vue.component("k-form", Form);
Vue.component("k-form-buttons", FormButtons);
Vue.component("k-form-indicator", FormIndicator);
Vue.component("k-field", Field);
Vue.component("k-fieldset", Fieldset);
Vue.component("k-input", Input);
Vue.component("k-login", Login);
Vue.component("k-login-code", LoginCode);
Vue.component("k-times", Times);
Vue.component("k-upload", Upload);
Vue.component("k-writer", Writer);

Vue.component("k-login-alert", LoginAlert);

Vue.component("k-structure-form", StructureForm);

Vue.component("k-toolbar", Toolbar);
Vue.component("k-toolbar-email-dialog", ToolbarEmailDialog);
Vue.component("k-toolbar-link-dialog", ToolbarLinkDialog);
