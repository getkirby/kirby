import { wrap } from "./array";

/**
 * Loads the default value for a field definition
 * @internal
 *
 * @param {Object} field
 * @returns {mixed}
 */
export function defaultValue(field) {
	if (field.default !== undefined) {
		return structuredClone(field.default);
	}

	const component =
		window.panel.app.$options.components[`k-${field.type}-field`];

	const valueProp = component?.options.props?.value;

	// if the field has no value prop,
	// it will be completely skipped
	if (valueProp === undefined) {
		return undefined;
	}

	const valuePropDefault = valueProp?.default;

	// resolve default prop functions
	if (typeof valuePropDefault === "function") {
		return valuePropDefault();
	}

	if (valuePropDefault !== undefined) {
		return valuePropDefault;
	}

	return null;
}

/**
 * Creates form values for provided fields
 * @internal
 *
 * @param {Object} fields
 * @returns {Object}
 */
export function form(fields) {
	const form = {};

	for (const fieldName in fields) {
		const defaultVal = defaultValue(fields[fieldName]);

		if (defaultVal !== undefined) {
			form[fieldName] = defaultVal;
		}
	}

	return form;
}

/**
 * Checks if a form field is visible based on its "when" conditions
 * and the current form values. Also works for sections.
 * @internal
 *
 * @param {Object} field - The form field object
 * @param {Object} values - The current form values object
 * @returns {boolean} - Whether the field is visible or not
 */
export function isVisible(field, values) {
	if (field.type === "hidden" || field.hidden === true) {
		return false;
	}

	if (!field.when) {
		return true;
	}

	for (const key in field.when) {
		const value = values[key.toLowerCase()];
		const condition = field.when[key];

		// if condition is checking for empty field
		if (
			value === undefined &&
			(condition === "" || (Array.isArray(condition) && condition.length === 0))
		) {
			continue;
		}

		if (value !== condition) {
			return false;
		}
	}

	return true;
}

/**
 * Adds an `[unknown]` option for each value
 * without a matching option, e.g. a deleted page
 * @internal
 *
 * @example
 * options([{ value: "a", text: "A" }], ["a", "b"])
 * // => [{ value: "a", text: "A" }, { value: "b", text: "[unknown]" }]
 *
 * @param {Array} options
 * @param {any} value
 * @returns {Array}
 */
export function options(options, value) {
	const known = options.map((option) => String(option.value));
	const unknown = wrap(value).filter(
		(value) =>
			value !== null &&
			value !== undefined &&
			value !== "" &&
			// inputs with a Boolean value prop cast a missing value to `false`
			value !== false &&
			known.includes(String(value)) === false
	);

	const text = `[${window.panel.$t("unknown")}]`;

	return [...options, ...unknown.map((value) => ({ text, value }))];
}

/**
 * Adds proper endpoint and section definitions
 * to subfields for a form field.
 * @internal
 *
 * @param {object} field
 * @param {object} fields
 * @returns {object}
 */
export function subfields(field, fields) {
	let subfields = {};

	for (const name in fields) {
		const subfield = fields[name];

		subfield.section = field.name;

		if (field.endpoints) {
			subfield.endpoints = {
				field: field.endpoints.field + "+" + name,
				section: field.endpoints.section,
				model: field.endpoints.model
			};
		}

		subfields[name] = subfield;
	}

	return subfields;
}

export default {
	defaultValue,
	form,
	isVisible,
	options,
	subfields
};
