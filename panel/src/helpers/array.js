import sort from "./sort";

/**
 * Array.fromObject()
 */
Array.fromObject = function (object) {
	return Array.isArray(object) ? object : Object.values(object ?? {});
};

/**
 * Array.sortBy()
 */
Array.prototype.sortBy = function (sortBy) {
	const options = sortBy.split(" ");
	const field = options[0];
	const direction = options[1] || "asc";

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
 * Array.split()
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
