/**
 * Clone provided object or array
 */
export function clone(array: object | []): object | [] {
	if (array === undefined) {
		return undefined;
	}

	return JSON.parse(JSON.stringify(array));
}

/**
 * Checks if value is empty
 */
export function isEmpty(value: any): boolean {
	if (value === undefined || value === null || value === "") {
		return true;
	}

	if (
		typeof value === "object" &&
		Object.keys(value).length === 0 &&
		value.constructor === Object
	) {
		return true;
	}

	if (value.length !== undefined && value.length === 0) {
		return true;
	}

	return false;
}

/**
 * Merges two objects
 */
export function merge(target: object, source: object): object {
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
