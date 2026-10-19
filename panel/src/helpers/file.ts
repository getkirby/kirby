import { format } from "./number";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

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
 * Creates a nice human-readable file size string with size unit,
 * matching `F::niceSize()` in the backend
 *
 * @example
 * niceSize(1024) // => "1 KB"
 * niceSize(1572864) // => "1.5 MB"
 */
export function niceSize(size: number): string {
	if (size <= 0) {
		return "0\u00a0KB";
	}

	const units = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
	const unit = Math.min(
		Math.floor(Math.log(size) / Math.log(1024)),
		units.length - 1
	);

	const value = format(size / 1024 ** unit, { maximumFractionDigits: 2 });

	return value + "\u00a0" + units[unit];
}

export default {
	extension,
	name,
	niceSize
};
