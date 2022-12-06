import CheckboxInput from "@/components/Forms/Input/CheckboxInput.vue";
import CheckboxesInput from "@/components/Forms/Input/CheckboxesInput.vue";
import DateInput from "@/components/Forms/Input/DateInput.vue";
import EmailInput from "@/components/Forms/Input/EmailInput.vue";
import ListInput from "@/components/Forms/Input/ListInput.vue";
import MultiselectInput from "@/components/Forms/Input/MultiselectInput.vue";
import NumberInput from "@/components/Forms/Input/NumberInput.vue";
import PasswordInput from "@/components/Forms/Input/PasswordInput.vue";
import RadioInput from "@/components/Forms/Input/RadioInput.vue";
import RangeInput from "@/components/Forms/Input/RangeInput.vue";
import SelectInput from "@/components/Forms/Input/SelectInput.vue";
import SlugInput from "@/components/Forms/Input/SlugInput.vue";
import TagsInput from "@/components/Forms/Input/TagsInput.vue";
import TelInput from "@/components/Forms/Input/TelInput.vue";
import TextInput from "@/components/Forms/Input/TextInput.vue";
import TextareaInput from "@/components/Forms/Input/TextareaInput.vue";
import TimeInput from "@/components/Forms/Input/TimeInput.vue";
import ToggleInput from "@/components/Forms/Input/ToggleInput.vue";
import TogglesInput from "@/components/Forms/Input/TogglesInput.vue";
import UrlInput from "@/components/Forms/Input/UrlInput.vue";

export default {
	install(app) {
		app.component("k-checkbox-input", CheckboxInput);
		app.component("k-checkboxes-input", CheckboxesInput);
		app.component("k-date-input", DateInput);
		app.component("k-email-input", EmailInput);
		app.component("k-list-input", ListInput);
		app.component("k-multiselect-input", MultiselectInput);
		app.component("k-number-input", NumberInput);
		app.component("k-password-input", PasswordInput);
		app.component("k-radio-input", RadioInput);
		app.component("k-range-input", RangeInput);
		app.component("k-select-input", SelectInput);
		app.component("k-slug-input", SlugInput);
		app.component("k-tags-input", TagsInput);
		app.component("k-tel-input", TelInput);
		app.component("k-text-input", TextInput);
		app.component("k-textarea-input", TextareaInput);
		app.component("k-time-input", TimeInput);
		app.component("k-toggle-input", ToggleInput);
		app.component("k-toggles-input", TogglesInput);
		app.component("k-url-input", UrlInput);
	}
};
