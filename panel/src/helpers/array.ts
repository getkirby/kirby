import sort from "./sort";
import "./regex";

/**
 * Creates an array from an object
 *
 * @example
 * fromObject({ a: 1, b: 2 }) // => [1, 2]
 * fromObject([1, 2]) // => [1, 2]
 */
export function fromObject<T>(
	object: T[] | Record<string, T> | null | undefined
): T[] {
	return Array.isArray(object) ? object : Object.values(object ?? {});
}

/**
 * Searches through an array by query,
 * matching against a string field of each item
 *
 * @example
 * search([...], "foo", { limit: 3 })
 * search([...], "foo", { field: "title" })
 */
export function search<T extends Record<string, unknown>>(
	array: T[],
	query: string | null | undefined,
	options: { min?: number; field?: string; limit?: number } = {}
): T[] {
	if (String(query).length <= (options.min ?? 0)) {
		return array;
	}

	// Filter options by query to retrieve items (no more than this.limit)
	const regex = new RegExp(RegExp.escape(query as string), "ig");
	const field = options.field ?? "text";

	const items = array.filter((item) => {
		// skip all items without the searched field
		if (!item[field]) {
			return false;
		}

		// match the search with the text
		return (item[field] as string).match(regex) !== null;
	});

	if (options.limit) {
		return items.slice(0, options.limit);
	}

	return items;
}

/**
 * Sorts an array of objects by a field and
 * optional direction ("asc" or "desc")
 *
 * @example
 * sortBy(items, "name asc")
 * sortBy(items, "date desc")
 */
export function sortBy<T extends Record<string, unknown>>(
	array: T[],
	sortBy: string
): T[] {
	const options = sortBy.split(" ");
	const field = options[0];
	const direction = options[1] ?? "asc";

	const sorter = sort({
		desc: direction === "desc",
		insensitive: true
	});

	return array.sort((a, b) => {
		const valueA = String(a[field] ?? "");
		const valueB = String(b[field] ?? "");
		return sorter(valueA, valueB);
	});
}

/**
 * Splits an array into sub-arrays at every occurrence of the delimiter.
 * The delimiter itself is not included in the output.
 *
 * @example
 * split(['a', 'b', '|', 'c'], '|') // => [['a', 'b'], ['c']]
 */
export function split<T>(array: T[], delimiter: string): T[][] {
	return array.reduce<T[][]>(
		(entries, entry) => {
			if (entry === delimiter) {
				entries.push([]);
			} else {
				entries[entries.length - 1].push(entry);
			}
			return entries;
		},
		[[]]
	);
}

/**
 * Ensures the input is an array or
 * is wrapped in an array otherwise
 *
 * @example
 * wrap('a') // => ['a']
 * wrap(['a']) // => ['a']
 */
export function wrap<T>(array: T | T[]): T[] {
	return Array.isArray(array) ? array : [array];
}

export default {
	fromObject,
	search,
	sortBy,
	split,
	wrap
};
