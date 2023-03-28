import { clone } from "./object.js";

/**
 * Creates form values for provided fields
 * @param {Object} fields
 * @returns {Object}
 */
export function form(fields) {
	const form = {};

	for (const fieldName in fields) {
		form[fieldName] = clone(fields[fieldName].default);
	}

	return form;
}

/**
 * Evaluates the when option and field
 * type to check if a field should be
 * visible. Also works for sections.
 *
 * @param {object} field
 * @param {array} values
 * @returns {boolean}
 */
export function isVisible(field, values) {
	if (field.hidden === true) {
		return false;
	}

	if (!field.when) {
		return true;
	}

	for (const key in field.when) {
		const value = values[key.toLowerCase()];
		const condition = field.when[key];

		// if condition is checking for empty field
		if (value === undefined && (condition === "" || condition === [])) {
			continue;
		}

		if (value !== condition) {
			return false;
		}
	}

	return true;
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
	form,
	isVisible,
	subfields
};
