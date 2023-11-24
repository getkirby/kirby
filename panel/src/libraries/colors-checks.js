import { isObject } from "@/helpers/object.js";

export const RE_HEX = /^#?([\da-f]{3}){1,2}$/i;
export const RE_HEXA = /^#?([\da-f]{4}){1,2}$/i;
export const RE_RGB =
	/^rgba?\(\s*(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i;
export const RE_HSL =
	/^hsla?\(\s*(\d{1,3}\.?\d*)(deg|rad|grad|turn)?(?:,|\s)+(\d{1,3})%(?:,|\s)+(\d{1,3})%(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i;

/**
 * Checks if input is a HEX/A string
 * @since 4.0.0
 *
 * @param {string|object} color
 * @returns {boolean}
 */
export function isHex(color) {
	return (
		typeof color === "string" && (RE_HEX.test(color) || RE_HEXA.test(color))
	);
}

/**
 * Checks if input is an RGB object
 * @since 4.0.0
 *
 * @param {string|object} color
 * @returns {boolean}
 */
export function isRgb(color) {
	return isObject(color) && "r" in color && "g" in color && "b" in color;
}

/**
 * Checks if input is an HSL object
 * @since 4.0.0
 *
 * @param {object} color
 * @returns {boolean}
 */
export function isHsl(color) {
	return isObject(color) && "h" in color && "s" in color && "l" in color;
}

/**
 * Checks if input is an HSV object
 * @since 4.0.0
 *
 * @param {string|object} color
 * @returns {boolean}
 */
export function isHsv(color) {
	return isObject(color) && "h" in color && "s" in color && "v" in color;
}
