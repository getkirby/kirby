/**
 * Attributes arrive as strings, Vue bindings as raw values and both
 * can be missing, so the setters have to take whatever they are given
 */
type Booleanish = boolean | string | null | undefined;
type Numberish = number | string | null | undefined;

function number(value: Numberish): number | null {
	if (value === null || value === undefined || value === "") {
		return null;
	}

	const result = Number(value);

	return Number.isNaN(result) === true ? null : result;
}

/**
 * Helper element adding native validation for required,
 * min and/or max to anything that can be counted, e.g. a list of models
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default class Validator extends HTMLElement {
	static formAssociated = true;
	internals: ElementInternals = this.attachInternals();

	#count: number = 0;
	#max: number | null = null;
	#min: number | null = null;
	#required: boolean = false;

	static get observedAttributes() {
		return ["count", "max", "min", "required"];
	}

	attributeChangedCallback(
		attribute: string,
		oldValue: string | null,
		newValue: string | null
	) {
		if (attribute === "required") {
			this.required = newValue;
		} else if (
			attribute === "count" ||
			attribute === "min" ||
			attribute === "max"
		) {
			this[attribute] = newValue;
		}
	}

	checkValidity() {
		return this.internals.checkValidity();
	}

	connectedCallback() {
		this.validate();
	}

	get count(): number {
		return this.#count;
	}

	set count(count: Numberish) {
		this.#count = number(count) ?? 0;
		this.validate();
	}

	get form() {
		return this.internals.form;
	}

	get input(): HTMLElement | null {
		const anchor = this.getAttribute("anchor");

		if (anchor) {
			const input = this.querySelector(anchor);

			if (input) {
				return input as HTMLElement;
			}
		}

		return (
			this.querySelector("input, textarea, select, button") ??
			(this.firstElementChild as HTMLElement | null)
		);
	}

	get max(): number | null {
		return this.#max;
	}

	set max(max: Numberish) {
		this.#max = number(max);
		this.validate();
	}

	get min(): number | null {
		return this.#min;
	}

	set min(min: Numberish) {
		this.#min = number(min);
		this.validate();
	}

	get name() {
		return this.getAttribute("name");
	}

	reportValidity() {
		return this.internals.reportValidity();
	}

	get required(): boolean {
		return this.#required;
	}

	set required(required: Booleanish) {
		this.#required =
			typeof required === "string" ? required !== "false" : required === true;
		this.validate();
	}

	get type() {
		return this.localName;
	}

	validate() {
		// setValidity only takes an element or nothing at all
		const anchor = this.input ?? undefined;

		if (this.required === true && this.count === 0) {
			this.internals.setValidity(
				{ valueMissing: true },
				window.panel.t("error.validation.required"),
				anchor
			);
		} else if (this.min !== null && this.count < this.min) {
			this.internals.setValidity(
				{ rangeUnderflow: true },
				window.panel.t("error.validation.min", { min: this.min }),
				anchor
			);
		} else if (this.max !== null && this.count > this.max) {
			this.internals.setValidity(
				{ rangeOverflow: true },
				window.panel.t("error.validation.max", { max: this.max }),
				anchor
			);
		} else {
			this.internals.setValidity({});
		}
	}

	get validationMessage() {
		return this.internals.validationMessage;
	}

	get validity() {
		return this.internals.validity;
	}

	get willValidate() {
		return this.internals.willValidate;
	}
}
