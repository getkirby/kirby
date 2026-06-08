import { describe, expect, it } from "vitest";
import { convert, parse, parseAs, toString } from "./colors";
import type {
	Color,
	ColorFormat,
	HexColor,
	HslColor,
	RgbColor
} from "./colors";

describe("colors.convert()", () => {
	const red = {
		hex: "#ff0000" as HexColor,
		rgb: { r: 255, g: 0, b: 0, a: 1 },
		hsl: { h: 0, s: 1, l: 0.5, a: 1 },
		hsv: { h: 0, s: 1, v: 1, a: 1 }
	};

	it.each<{
		name: string;
		color: Color;
		format: ColorFormat;
		expected: Color;
	}>([
		{ name: "hex → rgb", color: red.hex, format: "rgb", expected: red.rgb },
		{ name: "hex → hsl", color: red.hex, format: "hsl", expected: red.hsl },
		{ name: "hex → hsv", color: red.hex, format: "hsv", expected: red.hsv },
		{ name: "hex → hex", color: red.hex, format: "hex", expected: red.hex },
		{ name: "rgb → hex", color: red.rgb, format: "hex", expected: red.hex },
		{ name: "rgb → rgb", color: red.rgb, format: "rgb", expected: red.rgb },
		{ name: "rgb → hsl", color: red.rgb, format: "hsl", expected: red.hsl },
		{ name: "rgb → hsv", color: red.rgb, format: "hsv", expected: red.hsv },
		{ name: "hsl → hex", color: red.hsl, format: "hex", expected: red.hex },
		{ name: "hsl → rgb", color: red.hsl, format: "rgb", expected: red.rgb },
		{ name: "hsl → hsl", color: red.hsl, format: "hsl", expected: red.hsl },
		{ name: "hsl → hsv", color: red.hsl, format: "hsv", expected: red.hsv },
		{ name: "hsv → hex", color: red.hsv, format: "hex", expected: red.hex },
		{ name: "hsv → rgb", color: red.hsv, format: "rgb", expected: red.rgb },
		{ name: "hsv → hsl", color: red.hsv, format: "hsl", expected: red.hsl },
		{ name: "hsv → hsv", color: red.hsv, format: "hsv", expected: red.hsv }
	])("converts $name", ({ color, format, expected }) => {
		expect(convert(color, format)).toEqual(expected);
	});

	it("adds a missing leading # to hex input", () => {
		expect(convert("ff0000", "hex")).toBe("#ff0000");
	});

	it("throws for an unconvertible color", () => {
		expect(() => convert({} as unknown as Color, "hex")).toThrow(
			"Invalid color conversion"
		);
	});
});

describe("colors.parse()", () => {
	describe("hex", () => {
		const tests: [string, HexColor | null][] = [
			["#fff", "#fff"],
			["#fffa", "#fffa"],
			["#ffffff", "#ffffff"],
			["#ffffffaa", "#ffffffaa"],
			["fff", "#fff"],
			["fffa", "#fffa"],
			["ffffff", "#ffffff"],
			["ffffffaa", "#ffffffaa"],
			["ff", null]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(parse(input)).toStrictEqual(expected);
			expect(parseAs(input)).toStrictEqual(expected);
		});
	});

	describe("rgb", () => {
		const tests: [string, RgbColor | null][] = [
			["rgb(255, 255, 255)", { r: 255, g: 255, b: 255, a: 1 }],
			["rgb(255 255 255 )", { r: 255, g: 255, b: 255, a: 1 }],
			["rgb( 100% 50% 255)", { r: 255, g: 128, b: 255, a: 1 }],
			["rgb(0% 0% 100%)", { r: 0, g: 0, b: 255, a: 1 }],
			["rgba(255, 255, 255)", { r: 255, g: 255, b: 255, a: 1 }],
			["rgba(255, 255, 255", { r: 255, g: 255, b: 255, a: 1 }],
			["rgba(255, 255, 255, .4)", { r: 255, g: 255, b: 255, a: 0.4 }],
			["rgba(255 255 255 / .4)", { r: 255, g: 255, b: 255, a: 0.4 }],
			["rgba(255 255 255 / 40%)", { r: 255, g: 255, b: 255, a: 0.4 }],
			["rgba(255, 255,.4)", null],
			["rgba(255 / 255 / 255)", null]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(parse(input)).toStrictEqual(expected);
			expect(parseAs(input)).toStrictEqual(expected);
		});
	});

	describe("hsl", () => {
		const tests: [string, HslColor | null][] = [
			["hsl(255, 90%, 80%)", { h: 255, s: 0.9, l: 0.8, a: 1 }],
			["hsl( 255deg, 90%, 80%)", { h: 255, s: 0.9, l: 0.8, a: 1 }],
			["hsl(1.0472rad, 90%, 80%)", { h: 60, s: 0.9, l: 0.8, a: 1 }],
			["hsl(200grad, 90%, 80%)", { h: 180, s: 0.9, l: 0.8, a: 1 }],
			["hsl(0.5turn, 90%, 80%)", { h: 180, s: 0.9, l: 0.8, a: 1 }],
			["hsl(255 90% 80%  )", { h: 255, s: 0.9, l: 0.8, a: 1 }],
			["hsl(255 90% 80% / .7)", { h: 255, s: 0.9, l: 0.8, a: 0.7 }],
			["hsl(255 90% 80% / 70%)", { h: 255, s: 0.9, l: 0.8, a: 0.7 }],
			["hsla(255, 90%, 80%)", { h: 255, s: 0.9, l: 0.8, a: 1 }],
			["hsla(255, 90%, 80%", { h: 255, s: 0.9, l: 0.8, a: 1 }],
			["hsla(255, 90%, 80%, .4)", { h: 255, s: 0.9, l: 0.8, a: 0.4 }],
			["hsla(255, 90%,.4)", null]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(parse(input)).toStrictEqual(expected);
			expect(parseAs(input)).toStrictEqual(expected);
		});
	});

	it("returns false for empty or non-string input", () => {
		expect(parse("")).toBe(false);
		expect(parse(null as unknown as string)).toBe(false);
	});

	it("returns null for an unrecognized string", () => {
		expect(parse("not a color")).toBeNull();
	});
});

describe("colors.parseAs", () => {
	describe("hex", () => {
		const tests: [string, HexColor][] = [
			["#3a5", "#3a5"],
			["#3a5b", "#3a5b"],
			["#33aa55", "#33aa55"],
			["#33aa55bb", "#33aa55bb"],
			["rgb(51, 170, 85)", "#33aa55"],
			["rgb(51 170 85)", "#33aa55"],
			["rgb(100% 50% 255)", "#ff80ff"],
			["rgba(51,170 85)", "#33aa55"],
			["rgba(255 255 255 / .4)", "#ffffff66"],
			["hsl(180, 90%, 80%)", "#9ef9f9"],
			["hsl(180deg, 90%, 80%)", "#9ef9f9"],
			["hsl(200grad, 90%, 80%)", "#9ef9f9"],
			["hsl(180 90% 80% / .7)", "#9ef9f9b3"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(parseAs(input, "hex")).toStrictEqual(expected);
		});
	});

	describe("rgb", () => {
		const tests: [string, RgbColor][] = [
			["rgb(51, 170, 85)", { r: 51, g: 170, b: 85, a: 1 }],
			["rgb(51 170 85)", { r: 51, g: 170, b: 85, a: 1 }],
			["rgb(100% 50% 255)", { r: 255, g: 128, b: 255, a: 1 }],
			["rgba(51,170 85)", { r: 51, g: 170, b: 85, a: 1 }],
			["rgba(255 255 255 / .4)", { r: 255, g: 255, b: 255, a: 0.4 }]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(parseAs(input, "rgb")).toStrictEqual(expected);
		});
	});
});

describe("colors.toString()", () => {
	describe("hex", () => {
		const tests: [HexColor, string][] = [["#ffa", "#ffa"]];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input)).toStrictEqual(expected);
		});
	});

	describe("{rgb}", () => {
		const tests: [RgbColor, string][] = [
			[{ r: 51, g: 170, b: 85, a: 1 }, "rgb(51 170 85)"],
			[{ r: 51, g: 170, b: 85, a: 1 }, "rgb(51 170 85)"],
			[{ r: 51, g: 220, b: 85, a: 0.4 }, "rgb(51 220 85 / 0.40)"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input)).toStrictEqual(expected);
		});
	});

	describe("rgb", () => {
		const tests: [string, string][] = [
			["rgba(51, 170, 85)", "rgb(51 170 85)"],
			["rgba(  51, 220, 85,  .4 )", "rgb(51 220 85 / 0.40)"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input)).toStrictEqual(expected);
		});
	});

	describe("{hsl}", () => {
		const tests: [HslColor, string][] = [
			[{ h: 51, s: 0.3, l: 0.7 }, "hsl(51 30% 70%)"],
			[{ h: 51, s: 0.3, l: 0.7, a: 1 }, "hsl(51 30% 70%)"],
			[{ h: 51, s: 0.3, l: 0.7, a: 0.4 }, "hsl(51 30% 70% / 0.40)"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input)).toStrictEqual(expected);
		});
	});

	describe("rgb -> hex", () => {
		const tests: [string, string][] = [
			["rgba(51, 170, 85)", "#33aa55"],
			["rgba(  51, 220, 85,  .4 )", "#33dc5566"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input, "hex")).toStrictEqual(expected);
		});
	});

	describe("no alpha", () => {
		const tests: [string, string][] = [
			["#33dc5566", "#33dc55"],
			["rgba(  51, 220, 85,  .4 )", "rgb(51 220 85)"]
		];

		it.each(tests)("%s should be %s", (input, expected) => {
			expect(toString(input, undefined, false)).toStrictEqual(expected);
		});
	});

	it("strips alpha from a short hex", () => {
		expect(toString("#ffaa", undefined, false)).toBe("#ffa");
	});

	it("keeps a full hex unchanged when stripping alpha", () => {
		expect(toString("#ffffff", undefined, false)).toBe("#ffffff");
	});

	it("throws for an unsupported color", () => {
		expect(() => toString({ h: 0, s: 1, v: 1, a: 1 })).toThrow(
			"Unsupported color"
		);
	});
});
