import {
	hex2rgb,
	rgb2hex,
	hex2hsl,
	hsl2hex,
	hex2hsv,
	hsv2hex,
	rgb2hsl,
	hsl2rgb,
	rgb2hsv,
	hsv2rgb,
	hsl2hsv,
	hsv2hsl,
	hue2deg
} from "./colors-func.js";

import { isHex, isRgb, isHsl, isHsv, RE_RGB, RE_HSL } from "./colors-checks.js";

/**
 * Converts a color into another color space
 * @since 4.0.0
 *
 * @param {string|object} color
 * @param {string} format hex, rgb, hsl or hsv
 * @returns {string|object}
 */
export function convert(color, format) {
	if (isHex(color) === true) {
		// ensure leading #
		if (color[0] !== "#") {
			color = "#" + color;
		}

		switch (format) {
			case "hex":
				return color;
			case "rgb":
				return hex2rgb(color);
			case "hsl":
				return hex2hsl(color);
			case "hsv":
				return hex2hsv(color);
		}
	}

	if (isRgb(color) === true) {
		switch (format) {
			case "hex":
				return rgb2hex(color);
			case "rgb":
				return color;
			case "hsl":
				return rgb2hsl(color);
			case "hsv":
				return rgb2hsv(color);
		}
	}

	if (isHsl(color) === true) {
		switch (format) {
			case "hex":
				return hsl2hex(color);
			case "rgb":
				return hsl2rgb(color);
			case "hsl":
				return color;
			case "hsv":
				return hsl2hsv(color);
		}
	}

	if (isHsv(color) === true) {
		switch (format) {
			case "hex":
				return hsv2hex(color);
			case "rgb":
				return hsv2rgb(color);
			case "hsl":
				return hsv2hsl(color);
			case "hsv":
				return color;
		}
	}

	throw new Error(
		`Invalid color conversion: ${JSON.stringify(color)} -> ${format}`
	);
}

/**
 * Tries to parse a string as HEX, RGB or HSL color
 * @since 4.0.0
 *
 * @param {string} string
 * @returns {object|string|null}
 */
export function parse(string) {
	let values;

	if (!string || typeof string !== "string") {
		return false;
	}

	// HEX
	if (isHex(string) === true) {
		if (string[0] !== "#") {
			string = "#" + string;
		}

		return string;
	}

	// RGB
	if ((values = string.match(RE_RGB))) {
		const color = {
			r: Number(values[1]),
			g: Number(values[3]),
			b: Number(values[5]),
			a: Number(values[7] || 1)
		};

		if (values[2] === "%") {
			color.r = Math.ceil(color.r * 2.55);
		}
		if (values[4] === "%") {
			color.g = Math.ceil(color.g * 2.55);
		}
		if (values[6] === "%") {
			color.b = Math.ceil(color.b * 2.55);
		}
		if (values[8] === "%") {
			color.a = color.a / 100;
		}

		return color;
	}

	// HSL
	if ((values = string.match(RE_HSL))) {
		let [h, angle, s, l, a] = values.slice(1);

		const color = {
			h: hue2deg(h, angle),
			s: Number(s) / 100,
			l: Number(l) / 100,
			a: Number(a || 1)
		};

		if (values[6] === "%") {
			color.a = color.a / 100;
		}

		return color;
	}

	return null;
}

/**
 * Parses the input string and coverts it
 * (if necessary) to the target color space
 * @since 4.0.0
 *
 * @param {string} string
 * @param {string} format hex, rgb, hsl or hsv
 * @returns {string|object|false}
 */
export function parseAs(string, format) {
	const color = parse(string);

	if (!color || !format) {
		return color;
	}

	return convert(color, format);
}

/**
 * Formats color as CSS string
 * @since 4.0.0
 *
 * @param {object|string} color
 * @param {string} format hex, rgb, hsl or hsv
 * @param {boolean} alpha
 * @returns {string}
 */
export function toString(color, format, alpha = true) {
	let value = color;

	if (typeof value === "string") {
		value = parse(color);
	}

	// convert color if necessary
	if (value && format) {
		value = convert(value, format);
	}

	// HEX
	if (isHex(value) === true) {
		if (alpha !== true) {
			if (value.length === 5) {
				// short form with alpha
				value = value.slice(0, 4);
			} else if (value.length > 7) {
				// long form with alpha
				value = value.slice(0, 7);
			}
		}

		return value.toLowerCase();
	}

	if (isRgb(value) === true) {
		const r = value.r.toFixed();
		const g = value.g.toFixed();
		const b = value.b.toFixed();
		const a = value.a?.toFixed(2);

		if (alpha && a && a < 1) {
			return `rgb(${r} ${g} ${b} / ${a})`;
		}

		return `rgb(${r} ${g} ${b})`;
	}

	if (isHsl(value) === true) {
		const h = value.h.toFixed();
		const s = (value.s * 100).toFixed();
		const l = (value.l * 100).toFixed();
		const a = value.a?.toFixed(2);

		if (alpha && a && a < 1) {
			return `hsl(${h} ${s}% ${l}% / ${a})`;
		}

		return `hsl(${h} ${s}% ${l}%)`;
	}

	throw new Error(`Unsupported color: ${JSON.stringify(color)}`);
}

export default {
	convert,
	parse,
	parseAs,
	toString
};
