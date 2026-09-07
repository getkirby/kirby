import Validator from "../Validator";

/**
 * Validator for groups of inputs or complex inputs, which
 * count their entries from the value they receive
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default class InputValidator extends Validator {
	entries: Array<unknown> = [];

	static get observedAttributes() {
		return ["max", "min", "required", "value"];
	}

	attributeChangedCallback(
		attribute: string,
		oldValue: string | null,
		newValue: string | null
	) {
		if (attribute === "value") {
			this.value = newValue;
			return;
		}

		super.attributeChangedCallback(attribute, oldValue, newValue);
	}

	has(value: unknown) {
		return this.entries.includes(value);
	}

	get value(): string {
		return JSON.stringify(this.entries);
	}

	set value(value: unknown) {
		const entries =
			typeof value === "string" && value !== "" ? JSON.parse(value) : value;

		if (Array.isArray(entries) === true) {
			this.entries = entries;
		} else if (entries === null || entries === undefined || entries === "") {
			this.entries = [];
		} else {
			this.entries = [entries];
		}

		this.count = this.entries.length;
	}
}
