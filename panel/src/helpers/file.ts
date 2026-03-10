/**
 * Extracts the extension
 *
 * @example
 * extension("image.jpg") // => "jpg"
 */
export function extension(filename: string): string {
	return filename.split(".").slice(-1).join("");
}

/**
 * Extracts the name without extension
 *
 * @example
 * name("image.jpg") // => "image"
 */
export function name(filename: string): string {
	return filename.split(".").slice(0, -1).join(".");
}

/**
 * Creates a nice human-readable file size string with size unit
 *
 * @example
 * niceSize(1024) // => "1KB"
 * niceSize(1048576) // => "1MB"
 */
export function niceSize(size: number): string {
	const formatter = Intl.NumberFormat("en", {
		notation: "compact",
		style: "unit",
		unit: "byte",
		unitDisplay: "narrow"
	});

	return formatter.format(size);
}

export default {
	extension,
	name,
	niceSize
};
