import { defineAsyncComponent } from "vue";

/**
 * Writer carries ProseMirror, which is the heaviest dependency
 * of the Panel and is only needed once an editor is actually rendered.
 * Loading it on demand keeps it out of the initial bundle.
 */
const WriterInput = defineAsyncComponent(() => import("./WriterInput.vue"));

/**
 * @deprecated 5.0.0 Use `k-writer-input` instead
 */
const Writer = defineAsyncComponent(() => {
	window.panel.deprecated(
		"`k-writer` will be removed in a future version. Use `k-writer-input` instead."
	);

	return import("./WriterInput.vue");
});

export { default as "k-alpha-input" } from "./AlphaInput.vue";
export { default as "k-calendar-input" } from "./CalendarInput.vue";
export { default as "k-checkbox-input" } from "./CheckboxInput.vue";
export { default as "k-checkboxes-input" } from "./CheckboxesInput.vue";
export { default as "k-choice-input" } from "./ChoiceInput.vue";
export { default as "k-colorname-input" } from "./ColornameInput.vue";
export { default as "k-coloroptions-input" } from "./ColoroptionsInput.vue";
export { default as "k-colorpicker-input" } from "./ColorpickerInput.vue";
export { default as "k-coords-input" } from "./CoordsInput.vue";
export { default as "k-date-input" } from "./DateInput.vue";
export { default as "k-email-input" } from "./EmailInput.vue";
export { default as "k-hue-input" } from "./HueInput.vue";
export { default as "k-list-input" } from "./ListInput.vue";
export { default as "k-multiselect-input" } from "./MultiselectInput.vue";
export { default as "k-number-input" } from "./NumberInput.vue";
export { default as "k-password-input" } from "./PasswordInput.vue";
export { default as "k-picklist-input" } from "./PicklistInput.vue";
export { default as "k-radio-input" } from "./RadioInput.vue";
export { default as "k-range-input" } from "./RangeInput.vue";
export { default as "k-search-input" } from "./SearchInput.vue";
export { default as "k-select-input" } from "./SelectInput.vue";
export { default as "k-slug-input" } from "./SlugInput.vue";
export { default as "k-string-input" } from "./StringInput.vue";
export { default as "k-tags-input" } from "./TagsInput.vue";
export { default as "k-tel-input" } from "./TelInput.vue";
export { default as "k-text-input" } from "./TextInput.vue";
export { default as "k-textarea-input" } from "./TextareaInput.vue";
export { default as "k-time-input" } from "./TimeInput.vue";
export { default as "k-timeoptions-input" } from "./TimeoptionsInput.vue";
export { default as "k-toggle-input" } from "./ToggleInput.vue";
export { default as "k-toggles-input" } from "./TogglesInput.vue";
export { default as "k-url-input" } from "./UrlInput.vue";
export { WriterInput as "k-writer-input" };

/** Keep k-calendar and k-times as legacy aliases */
export { default as "k-calendar" } from "./CalendarInput.vue";
export { default as "k-times" } from "./TimeoptionsInput.vue";

/** @deprecated */
export { Writer as "k-writer" };
