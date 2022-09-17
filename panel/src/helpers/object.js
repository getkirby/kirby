/**
 * Clone provided object or array
 *
 * @param {Object|array} array
 * @returns  {Object|array}
 */
export function clone(array) {
	if (array === undefined) {
		return undefined;
	}

	return JSON.parse(JSON.stringify(array));
}

/**
 * Checks if value is empty
 *
 * @param {mixed} value
 * @returns {bool}
 */
export function isEmpty(value, strict = true) {
	// check for native empty states
	if (value === undefined || value === null || value === "") {
		return true;
	}

	// object with no keys
	if (
		typeof value === "object" &&
		value.constructor === Object &&
		Object.keys(value).length === 0
	) {
		return true;
	}

	// non-strict:
	// object with no non-empty values
	if (
		strict === false &&
		typeof value === "object" &&
		value.constructor === Object &&
		Object.values(value).filter(Boolean).length === 0
	) {
		return true;
	}

	// arrays, strings...
	if (value.length !== undefined && value.length === 0) {
		return true;
	}

	return false;
}

/**
 * Merges two objects
 *
 * @param {Object} target
 * @param {Object} source
 * @returns {Object}
 */
export function merge(target, source) {
	// Iterate through `source` properties and if an `Object` set property to merge of `target` and `source` properties
	for (const key of Object.keys(source)) {
		if (source[key] instanceof Object) {
			Object.assign(source[key], merge(target[key] || {}, source[key]));
		}
	}

	// Join `target` and modified `source`
	Object.assign(target || {}, source);
	return target;
}

export default {
	clone,
	isEmpty,
	merge
};
