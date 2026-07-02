/**
 * Helper input element taking care of adding native validation for
 * required, min and/or max to groups of inputs or complex inputs
 */
export default class InputValidator extends HTMLElement {
	static formAssociated = true;
	/** @type {ElementInternals} */
	internals = this.attachInternals();
	/** @type {Array<unknown>} */
	entries = [];
	/** @type {number | null} */
	max = null;
	/** @type {number | null} */
	min = null;
	/** @type {boolean} */
	required = false;

	static get observedAttributes() {
		return ["min", "max", "required", "value"];
	}

	attributeChangedCallback(attribute, oldValue, newValue) {
		if (attribute === "required") {
			this.required = newValue !== null && newValue !== "false";
		} else if (attribute === "min" || attribute === "max") {
			this[attribute] = newValue === null ? null : parseInt(newValue);
		} else {
			this[attribute] = newValue;
		}
	}

	connectedCallback() {
		this.validate();
	}

	checkValidity() {
		return this.internals.checkValidity();
	}

	get form() {
		return this.internals.form;
	}

	has(value) {
		return this.entries.includes(value);
	}

	get input() {
		return (
			this.querySelector(this.getAttribute("anchor")) ??
			this.querySelector("input, textarea, select, button") ??
			this.firstElementChild
		);
	}

	get name() {
		return this.getAttribute("name");
	}

	reportValidity() {
		return this.internals.reportValidity();
	}

	get type() {
		return this.localName;
	}

	validate() {
		if (this.required && this.entries.length === 0) {
			this.internals.setValidity(
				{ valueMissing: true },
				window.panel.t("error.validation.required"),
				this.input
			);
		} else if (this.min !== null && this.entries.length < this.min) {
			this.internals.setValidity(
				{ rangeUnderflow: true },
				window.panel.t("error.validation.min", { min: this.min }),
				this.input
			);
		} else if (this.max !== null && this.entries.length > this.max) {
			this.internals.setValidity(
				{ rangeOverflow: true },
				window.panel.t("error.validation.max", { max: this.max }),
				this.input
			);
		} else {
			this.internals.setValidity({});
		}
	}

	get validity() {
		return this.internals.validity;
	}

	get validationMessage() {
		return this.internals.validationMessage;
	}

	get value() {
		return JSON.stringify(this.entries ?? []);
	}

	set value(value) {
		this.entries = (typeof value === "string" ? JSON.parse(value) : []) ?? [];
		this.validate();
	}

	get willValidate() {
		return this.internals.willValidate;
	}
}
