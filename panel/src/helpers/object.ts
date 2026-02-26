/**
 * Clone provided object or array
 *
 * @example
 * clone({ a: 1 }) // => { a: 1 }
 * clone([1, 2, 3]) // => [1, 2, 3]
 */
export function clone<T>(value: T): T;
export function clone(): undefined;
export function clone<T>(value?: T): T | undefined {
	if (value === undefined) {
		return undefined;
	}

	return structuredClone(value);
}

/**
 * Filters the object via a predicate callback
 * @since 5.0.0
 *
 * @example
 * filter({ a: 1, b: 2, c: 3 }, (value) => value > 1) // => { b: 2, c: 3 }
 * filter({ a: 1, b: 2 }, (_, key) => key === "a") // => { a: 1 }
 */
export function filter<T extends Record<string, unknown>>(
	object: T,
	predicate: (value: unknown, key: string) => boolean
): Partial<T> {
	return Object.fromEntries(
		Object.entries(object).filter(([key, value]) => predicate(value, key))
	) as Partial<T>;
}

/**
 * Checks if value is empty
 *
 * @example
 * isEmpty(null) // => true
 * isEmpty({}) // => true
 * isEmpty({ a: 1 }) // => false
 */
export function isEmpty(value: unknown): boolean {
	if (value === undefined || value === null || value === "") {
		return true;
	}

	if (isObject(value)) {
		if (length(value) === 0) {
			return true;
		}

		if ("length" in value && value.length === 0) {
			return true;
		}
	}

	if (Array.isArray(value) && value.length === 0) {
		return true;
	}

	return false;
}

/**
 * Checks if input is an object
 *
 * @example
 * isObject({}) // => true
 * isObject([]) // => false
 * isObject(null) // => false
 */
export function isObject(input: unknown): input is Record<string, unknown> {
	return (
		typeof input === "object" && input !== null && input.constructor === Object
	);
}

/**
 * Counts all keys in the object
 * @since 4.0.0
 *
 * @example
 * length({ a: 1, b: 2 }) // => 2
 * length({}) // => 0
 */
export function length(object?: object | null): number {
	return Object.keys(object ?? {}).length;
}

/**
 * Merges two objects recursively
 *
 * @example
 * merge({ a: 1 }, { b: 2 }) // => { a: 1, b: 2 }
 * merge({ a: { x: 1 } }, { a: { y: 2 } }) // => { a: { x: 1, y: 2 } }
 */
export function merge(
	target: Record<string, unknown> = {},
	source: Record<string, unknown> = {}
): Record<string, unknown> {
	// Iterate through `source` properties and if an `Object`
	// set property to merge of `target` and `source` properties
	for (const key in source) {
		if (source[key] instanceof Object) {
			Object.assign(
				source[key] as object,
				merge(
					target[key] as Record<string, unknown>,
					source[key] as Record<string, unknown>
				)
			);
		}
	}

	// Join `target` and modified `source`
	Object.assign(target, source);
	return target;
}

/**
 * Check if the objects are identical
 *
 * @example
 * same({ a: 1 }, { a: 1 }) // => true
 * same({ a: 1 }, { a: 2 }) // => false
 */
export function same(a: unknown, b: unknown): boolean {
	return JSON.stringify(a) === JSON.stringify(b);
}

/**
 * Converts to lowercase all keys in an object
 *
 * @example
 * toLowerKeys({ Foo: 1, BAR: 2 }) // => { foo: 1, bar: 2 }
 */
export function toLowerKeys<T>(obj: Record<string, T>): Record<string, T> {
	return Object.keys(obj).reduce<Record<string, T>>((item, key) => {
		item[key.toLowerCase()] = obj[key];
		return item;
	}, {});
}

export default {
	clone,
	filter,
	isEmpty,
	isObject,
	length,
	merge,
	same,
	toLowerKeys
};
