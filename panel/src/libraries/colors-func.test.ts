import { describe, expect, it } from "vitest";
import {
	hex2hsl,
	hex2hsv,
	hex2rgb,
	hsl2hex,
	hsl2hsv,
	hsl2rgb,
	hsv2hex,
	hsv2hsl,
	hsv2rgb,
	hue2deg,
	rgb2hex,
	rgb2hsl,
	rgb2hsv
} from "./colors-func";

describe("colors-func", () => {
	describe("hex2rgb()", () => {
		it.each([
			{ hex: "#ff0000", rgb: { r: 255, g: 0, b: 0, a: 1 } },
			{ hex: "#f00", rgb: { r: 255, g: 0, b: 0, a: 1 } }, // short notation
			{ hex: "ff0000", rgb: { r: 255, g: 0, b: 0, a: 1 } }, // no leading #
			{ hex: "#00ff0080", rgb: { r: 0, g: 255, b: 0, a: 0.5 } } // with alpha
		])("converts $hex", ({ hex, rgb }) => {
			expect(hex2rgb(hex)).toEqual(rgb);
		});

		it("throws for an invalid hex color", () => {
			expect(() => hex2rgb("nope")).toThrow("unknown hex color");
		});
	});

	describe("rgb2hex()", () => {
		it("converts opaque colors", () => {
			expect(rgb2hex({ r: 255, g: 0, b: 0 })).toBe("#ff0000");
		});

		it("appends alpha when below 1", () => {
			expect(rgb2hex({ r: 255, g: 0, b: 0, a: 0.5 })).toBe("#ff000080");
		});
	});

	describe("rgb2hsv()", () => {
		it.each([
			{
				name: "black",
				rgb: { r: 0, g: 0, b: 0, a: 1 },
				hsv: { h: 0, s: 0, v: 0, a: 1 }
			},
			{
				name: "red",
				rgb: { r: 255, g: 0, b: 0, a: 1 },
				hsv: { h: 0, s: 1, v: 1, a: 1 }
			},
			{
				name: "green",
				rgb: { r: 0, g: 255, b: 0, a: 1 },
				hsv: { h: 120, s: 1, v: 1, a: 1 }
			},
			{
				name: "blue",
				rgb: { r: 0, g: 0, b: 255, a: 1 },
				hsv: { h: 240, s: 1, v: 1, a: 1 }
			},
			{
				name: "magenta",
				rgb: { r: 255, g: 0, b: 255, a: 1 },
				hsv: { h: 300, s: 1, v: 1, a: 1 }
			}
		])("converts $name", ({ rgb, hsv }) => {
			expect(rgb2hsv(rgb)).toEqual(hsv);
		});
	});

	describe("hsv2rgb()", () => {
		it("converts to rgb", () => {
			expect(hsv2rgb({ h: 0, s: 1, v: 1, a: 1 })).toEqual({
				r: 255,
				g: 0,
				b: 0,
				a: 1
			});
		});
	});

	describe("rgb2hsl()", () => {
		it.each([
			{
				name: "black",
				rgb: { r: 0, g: 0, b: 0, a: 1 },
				hsl: { h: 0, s: 0, l: 0, a: 1 }
			},
			{
				name: "red",
				rgb: { r: 255, g: 0, b: 0, a: 1 },
				hsl: { h: 0, s: 1, l: 0.5, a: 1 }
			},
			{
				name: "green",
				rgb: { r: 0, g: 255, b: 0, a: 1 },
				hsl: { h: 120, s: 1, l: 0.5, a: 1 }
			},
			{
				name: "blue",
				rgb: { r: 0, g: 0, b: 255, a: 1 },
				hsl: { h: 240, s: 1, l: 0.5, a: 1 }
			},
			{
				name: "magenta",
				rgb: { r: 255, g: 0, b: 255, a: 1 },
				hsl: { h: 300, s: 1, l: 0.5, a: 1 }
			}
		])("converts $name", ({ rgb, hsl }) => {
			expect(rgb2hsl(rgb)).toEqual(hsl);
		});
	});

	describe("hsl2rgb()", () => {
		it("converts to rgb", () => {
			expect(hsl2rgb({ h: 0, s: 1, l: 0.5, a: 1 })).toEqual({
				r: 255,
				g: 0,
				b: 0,
				a: 1
			});
		});
	});

	describe("hsv2hsl()", () => {
		it.each([
			{
				name: "black (v = 0)",
				hsv: { h: 0, s: 0, v: 0, a: 1 },
				hsl: { h: 0, s: 0, l: 0, a: 1 }
			},
			{
				name: "white (s = 0, v = 1)",
				hsv: { h: 0, s: 0, v: 1, a: 1 },
				hsl: { h: 0, s: 1, l: 1, a: 1 }
			},
			{
				name: "red (general)",
				hsv: { h: 0, s: 1, v: 1, a: 1 },
				hsl: { h: 0, s: 1, l: 0.5, a: 1 }
			}
		])("converts $name", ({ hsv, hsl }) => {
			expect(hsv2hsl(hsv)).toEqual(hsl);
		});
	});

	describe("hsl2hsv()", () => {
		it.each([
			{
				name: "grayscale (saturation 0)",
				hsl: { h: 0, s: 0, l: 0.5, a: 1 },
				hsv: { h: 0, s: 0, v: 0.5, a: 1 }
			},
			{
				name: "saturated (l = 0.5)",
				hsl: { h: 0, s: 1, l: 0.5, a: 1 },
				hsv: { h: 0, s: 1, v: 1, a: 1 }
			},
			{
				name: "dark (l < 0.5)",
				hsl: { h: 0, s: 1, l: 0.25, a: 1 },
				hsv: { h: 0, s: 1, v: 0.5, a: 1 }
			}
		])("converts $name", ({ hsl, hsv }) => {
			expect(hsl2hsv(hsl)).toEqual(hsv);
		});
	});

	describe("hex wrappers (round-trip via red)", () => {
		it("hex2hsl / hsl2hex", () => {
			expect(hex2hsl("#ff0000")).toEqual({ h: 0, s: 1, l: 0.5, a: 1 });
			expect(hsl2hex({ h: 0, s: 1, l: 0.5, a: 1 })).toBe("#ff0000");
		});

		it("hex2hsv / hsv2hex", () => {
			expect(hex2hsv("#ff0000")).toEqual({ h: 0, s: 1, v: 1, a: 1 });
			expect(hsv2hex({ h: 0, s: 1, v: 1, a: 1 })).toBe("#ff0000");
		});
	});

	describe("hue2deg()", () => {
		it.each<{ hue: number; angle?: "grad" | "rad" | "turn"; deg: number }>([
			{ hue: 90, deg: 90 },
			{ hue: 200, angle: "grad", deg: 180 },
			{ hue: Math.PI, angle: "rad", deg: 180 },
			{ hue: 0.5, angle: "turn", deg: 180 }
		])("converts $hue ($angle)", ({ hue, angle, deg }) => {
			expect(hue2deg(hue, angle)).toBe(deg);
		});
	});
});
