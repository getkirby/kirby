import { isObject } from "@/helpers/object";
import { HexColor, HslColor, HsvColor, RgbColor } from "./colors";

export const RE_HEX = /^#?([\da-f]{3}){1,2}$/i;
export const RE_HEXA = /^#?([\da-f]{4}){1,2}$/i;
export const RE_RGB =
	/^rgba?\(\s*(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i;
export const RE_HSL =
	/^hsla?\(\s*(\d{1,3}\.?\d*)(deg|rad|grad|turn)?(?:,|\s)+(\d{1,3})%(?:,|\s)+(\d{1,3})%(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i;

/**
 * Checks if input is a HEX/A string
 * @since 4.0.0
 */
export function isHex(color: unknown): color is HexColor {
	return (
		typeof color === "string" && (RE_HEX.test(color) || RE_HEXA.test(color))
	);
}

/**
 * Checks if input is an RGB object
 * @since 4.0.0
 */
export function isRgb(color: unknown): color is RgbColor {
	return isObject(color) && "r" in color && "g" in color && "b" in color;
}

/**
 * Checks if input is an HSL object
 * @since 4.0.0
 */
export function isHsl(color: unknown): color is HslColor {
	return isObject(color) && "h" in color && "s" in color && "l" in color;
}

/**
 * Checks if input is an HSV object
 * @since 4.0.0
 */
export function isHsv(color: unknown): color is HsvColor {
	return isObject(color) && "h" in color && "s" in color && "v" in color;
}
