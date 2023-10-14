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
export function isEmpty(value) {
	if (value === undefined || value === null || value === "") {
		return true;
	}

	if (isObject(value) && length(value) === 0) {
		return true;
	}

	if (value.length === 0) {
		return true;
	}

	return false;
}

/**
 * Checks if input is an object
 *
 * @param {any} input
 * @returns {boolean}
 */
export function isObject(input) {
	return typeof input === "object" && input?.constructor === Object;
}

/**
 * Counts all keys in the object
 * @since 4.0.0
 *
 * @param {object} object
 * @returns int
 */
export function length(object) {
	return Object.keys(object ?? {}).length;
}

/**
 * Merges two objects
 *
 * @param {Object} target
 * @param {Object} source
 * @returns {Object}
 */
export function merge(target, source = {}) {
	// Iterate through `source` properties and if an `Object`
	// set property to merge of `target` and `source` properties
	for (const key in source) {
		if (source[key] instanceof Object) {
			Object.assign(source[key], merge(target[key] ?? {}, source[key]));
		}
	}

	// Join `target` and modified `source`
	Object.assign(target ?? {}, source);
	return target;
}

/**
 * Check if the objects are identical
 *
 * @param {object} a
 * @param {object} b
 * @returns {Boolean}
 */
export function same(a, b) {
	return JSON.stringify(a) === JSON.stringify(b);
}

/**
 * Converts to lowercase all keys in an object
 *
 * @param {Object} obj
 * @returns {Object}
 */
export function toLowerKeys(obj) {
	return Object.keys(obj).reduce((item, key) => {
		item[key.toLowerCase()] = obj[key];
		return item;
	}, {});
}

export default {
	clone,
	isEmpty,
	isObject,
	length,
	merge,
	same,
	toLowerKeys
};
