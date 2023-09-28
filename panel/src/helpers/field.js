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
 * Checks if a form field is visible based on its "when" conditions
 * and the current form values. Also works for sections.
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
	form,
	isVisible,
	subfields
};
