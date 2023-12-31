/**
 * Wrapper for form inputs that can have multiple values,
 * taking care of adding native validation
 */
export default class ArrayInput extends HTMLElement {
	static formAssociated = true;

	static get observedAttributes() {
		return ["min", "max", "required", "value"];
	}

	attributeChangedCallback(attribute, oldValue, newValue) {
		this[attribute] = newValue;
	}

	constructor() {
		super();
		this.internals = this.attachInternals();
		this.selected = [];

		this.max = null;
		this.min = null;
		this.required = false;
	}

	connectedCallback() {
		this.emit();
		this.validate();
	}

	add(value) {
		// don't add empty values
		if (
			value === undefined ||
			value === null ||
			(typeof value === "string" && value.length === 0)
		) {
			return false;
		}

		// don't add duplicates
		if (this.has(value) === true) {
			return false;
		}

		this.selected.push(value);
		this.validate();
		this.emit();
	}

	emit() {
		this.internals.setFormValue(this.selected);
		this.dispatchEvent(new CustomEvent("input"));
	}

	focus() {
		this.input().focus();
	}

	input() {
		const fallback = "input, textarea, select, button";
		const selector = this.getAttribute("input") ?? fallback;

		return (
			this.querySelector(selector) ??
			this.querySelector(fallback) ??
			this.querySelector(":scope > *")
		);
	}

	get isEmpty() {
		return this.selected.length === 0;
	}

	has(value) {
		return this.selected.includes(value);
	}

	remove(value) {
		this.selected = this.selected.filter((item) => item !== value);
		this.internals.setFormValue(this.selected);
		this.validate();
		this.emit();
	}

	validate() {
		let flags = {};
		let error = "";

		if (Boolean(this.required) === true && this.selected.length === 0) {
			flags.valueMissing = true;
			error = window.panel.t("error.validation.items.required");
		} else if (
			this.isEmpty === false &&
			this.min &&
			this.selected.length < this.min
		) {
			flags.rangeUnderflow = true;
			error = window.panel.t("error.validation.items.min", { min: this.min });
		} else if (
			this.isEmpty === false &&
			this.max &&
			this.selected.length > this.max
		) {
			flags.rangeOverflow = true;
			error = window.panel.t("error.validation.items.max", { max: this.max });
		}

		this.internals.setValidity(flags, error, this.input());
	}

	get value() {
		return JSON.stringify(this.selected ?? []);
	}

	set value(value) {
		this.selected = (typeof value === "string" ? JSON.parse(value) : []) ?? [];
		this.validate();
		this.emit();
	}
}
