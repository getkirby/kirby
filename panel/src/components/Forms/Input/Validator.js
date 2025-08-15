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
		this.tabIndex = 0;

		// pass-through the id attribute
		this.input.setAttribute("id", this.getAttribute("id"));
		this.removeAttribute("id");

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
			this.querySelector(":scope > *")
		);
	}

	get isEmpty() {
		return this.selected.length === 0;
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
		const max = parseInt(this.getAttribute("max"));
		const min = parseInt(this.getAttribute("min"));

		const required =
			this.hasAttribute("required") &&
			this.getAttribute("required") !== "false";

		if (required && this.entries.length === 0) {
			this.internals.setValidity(
				{ valueMissing: true },
				window.panel.$t("error.validation.required"),
				this.input
			);
		} else if (this.hasAttribute("min") && this.entries.length < min) {
			this.internals.setValidity(
				{ rangeUnderflow: true },
				window.panel.$t("error.validation.min", { min }),
				this.input
			);
		} else if (this.hasAttribute("max") && this.entries.length > max) {
			this.internals.setValidity(
				{ rangeOverflow: true },
				window.panel.$t("error.validation.max", { max }),
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
