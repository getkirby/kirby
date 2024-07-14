import sort from "./sort";
import "./regex";

/**
 * Creates an array from an object
 *
 * @param {Array|Object} object
 * @returns {Array}
 */
export function fromObject(object) {
	return Array.isArray(object) ? object : Object.values(object ?? {});
}

/**
 * Search through an array by query
 *
 * @param {Array} array
 * @param {String} query
 * @param {Object} options
 * @returns {Array}
 */
export const search = (array, query, options = {}) => {
	if ((query ?? "").length <= (options.min ?? 0)) {
		return array;
	}

	// Filter options by query to retrieve items (no more than this.limit)
	const regex = new RegExp(RegExp.escape(query), "ig");
	const field = options.field ?? "text";

	const items = array.filter((item) => {
		// skip all items without the searched field
		if (!item[field]) {
			return false;
		}

		// match the search with the text
		return item[field].match(regex) !== null;
	});

	if (options.limit) {
		return items.slice(0, options.limit);
	}

	return items;
};

/**
 * @param {Array} array
 * @param {String} sortBy
 * @returns {Array}
 */
export function sortBy(array, sortBy) {
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
 * @param {Array} array
 * @param {String} delimiter
 * @returns {Array}
 *
 */
export function split(array, delimiter) {
	return array.reduce(
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
 * @param {any} array
 * @returns {Array}
 */
export function wrap(array) {
	return Array.isArray(array) ? array : [array];
}

export default {
	fromObject,
	search,
	sortBy,
	split,
	wrap
};
