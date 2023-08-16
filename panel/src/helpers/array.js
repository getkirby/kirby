import sort from "./sort";
import "./regex";

/**
 * @param {Array} cunks
 * @param {Number} size
 * @returns {Array}
 *
 */
export function chunks(array, size) {
	const chunks = [];

	for (let i = 0; i < array.length; i += size) {
		chunks.push(array.slice(i, i + size));
	}

	return chunks;
}

/**
 * Array.fromObject()
 */
Array.fromObject = function (object) {
	return Array.isArray(object) ? object : Object.values(object ?? {});
};

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
 * myArray.sortBy()
 */
Array.prototype.sortBy = function (sortBy) {
	const options = sortBy.split(" ");
	const field = options[0];
	const direction = options[1] ?? "asc";

	return this.sort((a, b) => {
		const valueA = String(a[field]).toLowerCase();
		const valueB = String(b[field]).toLowerCase();

		if (direction === "desc") {
			return sort(valueB, valueA);
		}

		return sort(valueA, valueB);
	});
};

/**
 * myArray.split()
 *
 * @param {String} delimiter
 * @returns {Array}
 *
 */
Array.prototype.split = function (delimiter) {
	return this.reduce(
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
};

/**
 * Array.wrap()
 */
Array.wrap = function (array) {
	return Array.isArray(array) ? array : [array];
};

export default {
	chunks,
	search
};
