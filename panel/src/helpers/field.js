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
	subfields
};
