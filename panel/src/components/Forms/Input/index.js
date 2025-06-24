import AlphaInput from "./AlphaInput.vue";
import CalendarInput from "./CalendarInput.vue";
import CheckboxInput from "./CheckboxInput.vue";
import CheckboxesInput from "./CheckboxesInput.vue";
import ChoiceInput from "./ChoiceInput.vue";
import ColornameInput from "./ColornameInput.vue";
import ColoroptionsInput from "./ColoroptionsInput.vue";
import ColorpickerInput from "./ColorpickerInput.vue";
import CoordsInput from "./CoordsInput.vue";
import DateInput from "./DateInput.vue";
import EmailInput from "./EmailInput.vue";
import HueInput from "./HueInput.vue";
import ListInput from "./ListInput.vue";
import MultiselectInput from "./MultiselectInput.vue";
import NumberInput from "./NumberInput.vue";
import PasswordInput from "./PasswordInput.vue";
import PicklistInput from "./PicklistInput.vue";
import RadioInput from "./RadioInput.vue";
import RangeInput from "./RangeInput.vue";
import SelectInput from "./SelectInput.vue";
import SlugInput from "./SlugInput.vue";
import SearchInput from "./SearchInput.vue";
import StringInput from "./StringInput.vue";
import TagsInput from "./TagsInput.vue";
import TelInput from "./TelInput.vue";
import TextInput from "./TextInput.vue";
import TextareaInput from "./TextareaInput.vue";
import TimeInput from "./TimeInput.vue";
import TimeoptionsInput from "./TimeoptionsInput.vue";
import ToggleInput from "./ToggleInput.vue";
import TogglesInput from "./TogglesInput.vue";
import UrlInput from "./UrlInput.vue";
import WriterInput from "./WriterInput.vue";

import Validator from "./Validator.js";

/** @deprecated */
import Writer from "../Writer/Writer.vue";

export default {
	install(app) {
		customElements.define("k-input-validator", Validator);

		app.component("k-alpha-input", AlphaInput);
		app.component("k-calendar-input", CalendarInput);
		app.component("k-checkbox-input", CheckboxInput);
		app.component("k-checkboxes-input", CheckboxesInput);
		app.component("k-choice-input", ChoiceInput);
		app.component("k-colorname-input", ColornameInput);
		app.component("k-coloroptions-input", ColoroptionsInput);
		app.component("k-colorpicker-input", ColorpickerInput);
		app.component("k-coords-input", CoordsInput);
		app.component("k-date-input", DateInput);
		app.component("k-email-input", EmailInput);
		app.component("k-hue-input", HueInput);
		app.component("k-list-input", ListInput);
		app.component("k-multiselect-input", MultiselectInput);
		app.component("k-number-input", NumberInput);
		app.component("k-password-input", PasswordInput);
		app.component("k-picklist-input", PicklistInput);
		app.component("k-radio-input", RadioInput);
		app.component("k-range-input", RangeInput);
		app.component("k-search-input", SearchInput);
		app.component("k-select-input", SelectInput);
		app.component("k-slug-input", SlugInput);
		app.component("k-string-input", StringInput);
		app.component("k-tags-input", TagsInput);
		app.component("k-tel-input", TelInput);
		app.component("k-text-input", TextInput);
		app.component("k-textarea-input", TextareaInput);
		app.component("k-time-input", TimeInput);
		app.component("k-timeoptions-input", TimeoptionsInput);
		app.component("k-toggle-input", ToggleInput);
		app.component("k-toggles-input", TogglesInput);
		app.component("k-url-input", UrlInput);
		app.component("k-writer-input", WriterInput);

		/** Keep k-calendar and k-times as legacy aliases */
		app.component("k-calendar", CalendarInput);
		app.component("k-times", TimeoptionsInput);

		/** @deprecated */
		app.component("k-writer", Writer);
	}
};
