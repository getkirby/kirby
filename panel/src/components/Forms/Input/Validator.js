/**
 * Helper input element taking care of adding native validation for
 * required, min and/or max to groups of inputs or complex inputs
 */
export default class InputValidator extends HTMLElement {
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
		this.entries = [];

		this.max = null;
		this.min = null;
		this.required = false;
	}

	connectedCallback() {
		this.classList.add("input-hidden");
		this.validate();
	}

	// The following properties and methods aren't strictly required,
	// but browser-level form controls provide them. Providing them helps
	// ensure consistency with browser-provided controls.
	get form() {
		return this.internals.form;
	}

	get name() {
		return this.getAttribute("name");
	}

	checkValidity() {
		return this.internals.checkValidity();
	}

	has(value) {
		return this.entries.includes(value);
	}

	get isEmpty() {
		return this.selected.length === 0;
	}

	reportValidity() {
		return this.internals.reportValidity();
	}

	get type() {
		return this.localName;
	}

	validate() {
		const max = parseInt(this.getAttribute("max"));
		const min = parseInt(this.getAttribute("min"));

		const required =
			this.hasAttribute("required") &&
			this.getAttribute("required") !== "false";

		if (required && this.entries.length === 0) {
			this.internals.setValidity(
				{ valueMissing: true },
				window.panel.$t("error.validation.required")
			);
		} else if (this.hasAttribute("min") && this.entries.length < min) {
			this.internals.setValidity(
				{ rangeUnderflow: true },
				window.panel.$t("error.validation.min", { min })
			);
		} else if (this.hasAttribute("max") && this.entries.length > max) {
			this.internals.setValidity(
				{ rangeOverflow: true },
				window.panel.$t("error.validation.max", { max })
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
