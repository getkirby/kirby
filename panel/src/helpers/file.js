/**
 * Extracts the extension
 *
 * @param {String} filename
 * @returns {String}
 */
export const extension = (filename) => {
	return filename.split(".").slice(-1).join("");
};

/**
 * Extracts the name without extension
 *
 * @param {String} filename
 * @returns {String}
 */
export const name = (filename) => {
	return filename.split(".").slice(0, -1).join(".");
};

/**
 * Creates a nice human-readable file size string with size unit
 *
 * @param {Number} size
 * @returns {String}
 */
export const niceSize = (size) => {
	const formatter = Intl.NumberFormat("en", {
		notation: "compact",
		style: "unit",
		unit: "byte",
		unitDisplay: "narrow"
	});

	return formatter.format(size);
};

export default {
	extension,
	name,
	niceSize
};
