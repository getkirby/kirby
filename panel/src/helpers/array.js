import sort from "./sort";

/**
 * Array.fromObject()
 */
Array.prototype.fromObject = function (object) {
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
 * Array.wrap()
 */
Array.prototype.wrap = function (array) {
	return Array.isArray(array) ? array : [array];
};

export default {};
