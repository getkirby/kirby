import { describe, expect, it } from "vitest";
import { parse, parseAs, toString } from "./colors.js";

describe("colors.parse(hex)", () => {
	const tests = [
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

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(parse(test[0])).toStrictEqual(test[1]);
			expect(parseAs(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.parse(rgb)", () => {
	const tests = [
		["rgb(255, 255, 255)", { r: 255, g: 255, b: 255, a: 1 }],
		["rgb(255 255 255 )", { r: 255, g: 255, b: 255, a: 1 }],
		["rgb( 100% 50% 255)", { r: 255, g: 128, b: 255, a: 1 }],
		["rgba(255, 255, 255)", { r: 255, g: 255, b: 255, a: 1 }],
		["rgba(255, 255, 255", { r: 255, g: 255, b: 255, a: 1 }],
		["rgba(255, 255, 255, .4)", { r: 255, g: 255, b: 255, a: 0.4 }],
		["rgba(255 255 255 / .4)", { r: 255, g: 255, b: 255, a: 0.4 }],
		["rgba(255 255 255 / 40%)", { r: 255, g: 255, b: 255, a: 0.4 }],
		["rgba(255, 255,.4)", null],
		["rgba(255 / 255 / 255)", null]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(parse(test[0])).toStrictEqual(test[1]);
			expect(parseAs(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.parse(hsl)", () => {
	const tests = [
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

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(parse(test[0])).toStrictEqual(test[1]);
			expect(parseAs(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.parseAs(hex)", () => {
	const tests = [
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

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(parseAs(test[0], "hex")).toStrictEqual(test[1]);
		});
	}
});

describe("colors.parseAs(rgb)", () => {
	const tests = [
		["rgb(51, 170, 85)", { r: 51, g: 170, b: 85, a: 1 }],
		["rgb(51 170 85)", { r: 51, g: 170, b: 85, a: 1 }],
		["rgb(100% 50% 255)", { r: 255, g: 128, b: 255, a: 1 }],
		["rgba(51,170 85)", { r: 51, g: 170, b: 85, a: 1 }],
		["rgba(255 255 255 / .4)", { r: 255, g: 255, b: 255, a: 0.4 }]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(parseAs(test[0], "rgb")).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString(hex)", () => {
	const tests = [["#ffa", "#ffa"]];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString({rgb})", () => {
	const tests = [
		[{ r: 51, g: 170, b: 85, a: 1 }, "rgb(51 170 85)"],
		[{ r: 51, g: 170, b: 85, a: 1 }, "rgb(51 170 85)"],
		[{ r: 51, g: 220, b: 85, a: 0.4 }, "rgb(51 220 85 / 0.40)"]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString(rgb)", () => {
	const tests = [
		["rgba(51, 170, 85)", "rgb(51 170 85)"],
		["rgba(  51, 220, 85,  .4 )", "rgb(51 220 85 / 0.40)"]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString({hsl})", () => {
	const tests = [
		[{ h: 51, s: 0.3, l: 0.7 }, "hsl(51 30% 70%)"],
		[{ h: 51, s: 0.3, l: 0.7, a: 1 }, "hsl(51 30% 70%)"],
		[{ h: 51, s: 0.3, l: 0.7, a: 0.4 }, "hsl(51 30% 70% / 0.40)"]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0])).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString(rgb -> hex)", () => {
	const tests = [
		["rgba(51, 170, 85)", "#33aa55"],
		["rgba(  51, 220, 85,  .4 )", "#33dc5566"]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0], "hex")).toStrictEqual(test[1]);
		});
	}
});

describe("colors.toString() - no alpha", () => {
	const tests = [
		["#33dc5566", "#33dc55"],
		["rgba(  51, 220, 85,  .4 )", "rgb(51 220 85)"]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(toString(test[0], null, false)).toStrictEqual(test[1]);
		});
	}
});
