import { clone } from "./object";

type Field = Record<string, unknown>;

/**
 * Loads the default value for a field definition
 * @unstable
 *
 * @example
 * defaultValue({ type: "text", default: "Hello" }) // => "Hello"
 * defaultValue({ type: "text" }) // => null
 */
export function defaultValue(field: Field): unknown {
	if (field.default !== undefined) {
		return clone(field.default);
	}

	// TODO: Remove once window.panel is globally typed
	// @ts-expect-error - window.panel has no type yet
	const component = window.panel.app.component(`k-${field.type}-field`);
	const valueProp = component?.props?.value;

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
 * @unstable
 *
 * @example
 * form({ title: { type: "text", default: "Hello" }, age: { type: "number" } })
 * // => { title: "Hello" }
 */
export function form(fields: Record<string, Field>): Record<string, unknown> {
	const form: Record<string, unknown> = {};

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
 * @unstable
 *
 * @example
 * isVisible({ type: "text", when: { status: "draft" } }, { status: "draft" }) // => true
 * isVisible({ type: "text", when: { status: "draft" } }, { status: "published" }) // => false
 *
 * @param field - The form field object
 * @param values - The current form values object
 */
export function isVisible(
	field: Field,
	values: Record<string, unknown>
): boolean {
	if (field.type === "hidden" || field.hidden === true) {
		return false;
	}

	if (!field.when) {
		return true;
	}

	const when = field.when as Record<string, unknown>;

	for (const key in when) {
		const value = values[key.toLowerCase()];
		const condition = when[key];

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
 * Adds proper endpoint and section definitions
 * to subfields for a form field.
 * @unstable
 */
export function subfields(
	field: Field,
	fields: Record<string, Field>
): Record<string, Field> {
	const subfields: Record<string, Field> = {};

	for (const name in fields) {
		const subfield = fields[name];

		subfield.section = field.name;

		if (field.endpoints) {
			const endpoints = field.endpoints as {
				field: string;
				section: string;
				model: string;
			};

			subfield.endpoints = {
				field: endpoints.field + "+" + name,
				section: endpoints.section,
				model: endpoints.model
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
	subfields
};
