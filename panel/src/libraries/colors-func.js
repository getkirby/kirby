import { RE_HEX, RE_HEXA } from "./colors-checks.js";

export function hsv2hsl({ h, s, v, a }) {
	if (v === 0) {
		return { h, s: 0, l: 0, a };
	}

	if (s === 0 && v === 1) {
		return { h, s: 1, l: 1, a };
	}

	const l = (v * (2 - s)) / 2;
	s = (v * s) / (1 - Math.abs(2 * l - 1));

	return { h, s, l, a };
}

export function hsl2hsv({ h, s, l, a }) {
	const v1 = s * (l < 0.5 ? l : 1 - l);
	s = v1 === 0 ? 0 : (2 * v1) / (l + v1);
	const v = l + v1;
	return { h, s, v, a };
}

export function hsv2hex(hsv) {
	return hsl2hex(hsv2hsl(hsv));
}

export function hex2hsv(hex) {
	return hsl2hsv(hex2hsl(hex));
}

export function hex2rgb(hex) {
	if (RE_HEX.test(hex) === true || RE_HEXA.test(hex) === true) {
		// remove leading #
		if (hex[0] === "#") {
			hex = hex.slice(1);
		}

		// expand short-notation to full six-digit
		if (hex.length === 3) {
			hex = hex.split("").reduce((x, y) => x + y + y, "");
		}

		const num = parseInt(hex, 16);

		// without alpha (#ff00ff or #f0f)
		if (RE_HEX.test(hex) === true) {
			return {
				r: num >> 16,
				g: (num >> 8) & 0xff,
				b: num & 0xff,
				a: 1
			};
		}

		// with alpha (e.g. #ffaa0088)
		return {
			r: (num >> 24) & 0xff,
			g: (num >> 16) & 0xff,
			b: (num >> 8) & 0xff,
			a: Math.round(((num & 0xff) / 0xff) * 100) / 100
		};
	}

	throw new Error(`unknown hex color: ${hex}`);
}

export function rgb2hex({ r, g, b, a = 1 }) {
	let hex = "#" + ((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1);

	if (a < 1) {
		hex += ((1 << 8) | Math.round(a * 255)).toString(16).slice(1);
	}

	return hex;
}

export function rgb2hsv({ r, g, b, a }) {
	r /= 255;
	g /= 255;
	b /= 255;

	const v = Math.max(r, g, b);
	const c = v - Math.min(r, g, b);
	const s = v && c / v;

	let h =
		c && (v == r ? (g - b) / c : v == g ? 2 + (b - r) / c : 4 + (r - g) / c);
	h = 60 * (h < 0 ? h + 6 : h);

	return { h, s, v, a };
}

export function hsv2rgb({ h, s, v, a }) {
	const f = (n, k = (n + h / 60) % 6) =>
		v - v * s * Math.max(Math.min(k, 4 - k, 1), 0);
	return {
		r: f(5) * 255,
		g: f(3) * 255,
		b: f(1) * 255,
		a
	};
}

export function hsl2rgb({ h, s, l, a }) {
	const b = s * Math.min(l, 1 - l);
	const f = (n, k = (n + h / 30) % 12) =>
		l - b * Math.max(Math.min(k - 3, 9 - k, 1), -1);

	return {
		r: f(0) * 255,
		g: f(8) * 255,
		b: f(4) * 255,
		a
	};
}

export function rgb2hsl({ r, g, b, a }) {
	r /= 255;
	g /= 255;
	b /= 255;

	const v = Math.max(r, g, b);
	const c = v - Math.min(r, g, b);
	const f = 1 - Math.abs(v + v - c - 1);
	const s = f ? c / f : 0;
	const l = (v + v - c) / 2;

	let h =
		c && (v == r ? (g - b) / c : v == g ? 2 + (b - r) / c : 4 + (r - g) / c);
	h = 60 * (h < 0 ? h + 6 : h);

	return { h, s, l, a };
}

export function hsl2hex(hsl) {
	return rgb2hex(hsl2rgb(hsl));
}

export function hex2hsl(hex) {
	return rgb2hsl(hex2rgb(hex));
}

export function hue2deg(hue, angle) {
	hue = Number(hue);

	if (angle === "grad") {
		hue = hue * (180 / 200);
	} else if (angle === "rad") {
		hue = hue * (180 / Math.PI);
	} else if (angle === "turn") {
		hue = hue * 360;
	}

	return parseInt(hue % 360);
}
