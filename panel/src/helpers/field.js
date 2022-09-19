/**
 * Evaluates the when option and field
 * type to check if a field should be
 * visible. Also works for sections.
 *
 * @param {object} field
 * @returns {object}
 */
export function isVisible(field, values) {
	if (field.type === "hidden") {
		return false;
	}

	if (!field.when) {
		return true;
	}

	let result = true;

	Object.keys(field.when).forEach((key) => {
		const value = values[key.toLowerCase()];
		const condition = field.when[key];

		if (value !== condition) {
			result = false;
		}
	});

	return result;
}

/**
 * Adds proper endpoint and section definitions
 * to subfields for a form field.
 *
 * @param {object} field
 * @param {object} fields
 * @returns {object}
 */
export function subfields(field, fields) {
	let subfields = {};

	Object.keys(fields).forEach((name) => {
		let subfield = fields[name];

		subfield.section = field.name;
		subfield.endpoints = {
			field: field.endpoints.field + "+" + name,
			section: field.endpoints.section,
			model: field.endpoints.model
		};

		subfields[name] = subfield;
	});

	return subfields;
}

export default {
	isVisible,
	subfields
};
