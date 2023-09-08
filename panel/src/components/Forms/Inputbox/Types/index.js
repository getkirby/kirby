import CheckboxesInputbox from "./CheckboxesInputbox.vue";
import ColorInputbox from "./ColorInputbox.vue";
import DateInputbox from "./DateInputbox.vue";
import EmailInputbox from "./EmailInputbox.vue";
import ListInputbox from "./ListInputbox.vue";
import MultiselectInputbox from "./MultiselectInputbox.vue";
import NumberInputbox from "./NumberInputbox.vue";
import PasswordInputbox from "./PasswordInputbox.vue";
import RadioInputbox from "./RadioInputbox.vue";
import RangeInputbox from "./RangeInputbox.vue";
import SelectInputbox from "./SelectInputbox.vue";
import SlugInputbox from "./SlugInputbox.vue";
import TagsInputbox from "./TagsInputbox.vue";
import TelInputbox from "./TelInputbox.vue";
import TextInputbox from "./TextInputbox.vue";
import TextareaInputbox from "./TextareaInputbox.vue";
import ToggleInputbox from "./ToggleInputbox.vue";
import TogglesInputbox from "./TogglesInputbox.vue";
import TimeInputbox from "./TimeInputbox.vue";
import UrlInputbox from "./UrlInputbox.vue";
import WriterInputbox from "./WriterInputbox.vue";

export default {
	install(app) {
		app.component("k-checkboxes-inputbox", CheckboxesInputbox);
		app.component("k-color-inputbox", ColorInputbox);
		app.component("k-date-inputbox", DateInputbox);
		app.component("k-email-inputbox", EmailInputbox);
		app.component("k-list-inputbox", ListInputbox);
		app.component("k-multiselect-inputbox", MultiselectInputbox);
		app.component("k-number-inputbox", NumberInputbox);
		app.component("k-password-inputbox", PasswordInputbox);
		app.component("k-radio-inputbox", RadioInputbox);
		app.component("k-range-inputbox", RangeInputbox);
		app.component("k-select-inputbox", SelectInputbox);
		app.component("k-slug-inputbox", SlugInputbox);
		app.component("k-tags-inputbox", TagsInputbox);
		app.component("k-tel-inputbox", TelInputbox);
		app.component("k-text-inputbox", TextInputbox);
		app.component("k-textarea-inputbox", TextareaInputbox);
		app.component("k-time-inputbox", TimeInputbox);
		app.component("k-toggle-inputbox", ToggleInputbox);
		app.component("k-toggles-inputbox", TogglesInputbox);
		app.component("k-url-inputbox", UrlInputbox);
		app.component("k-writer-inputbox", WriterInputbox);
	}
};
