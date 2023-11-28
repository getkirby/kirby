import { describe, expect, it } from "vitest";
import { isHex, isRgb, isHsl, isHsv } from "./colors-checks.js";

describe("colors.isHex", () => {
	const tests = [
		["#fff", true],
		["#fffa", true],
		["#ffffff", true],
		["#ffffffaa", true],
		["fff", true],
		["fffa", true],
		["ffffff", true],
		["ffffffaa", true],
		["ff", false],
		["rgba(255, 255, 255)", false]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(isHex(test[0])).toBe(test[1]);
		});
	}
});

describe("colors.isRgb", () => {
	const tests = [
		[{ r: 0, g: 0, b: 0 }, true],
		[{ r: 0, g: 0, b: 0, a: 1 }, true],
		[{ r: 0, g: 0, a: 1 }, false],
		["rgb( 0, 0, 0)", false]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(isRgb(test[0])).toBe(test[1]);
		});
	}
});

describe("colors.isHsl", () => {
	const tests = [
		[{ h: 0, s: 0, l: 0 }, true],
		[{ h: 0, s: 0, l: 0, a: 1 }, true],
		[{ h: 0, s: 0, a: 1 }, false],
		["hsla( 0, 0, 0)", false]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(isHsl(test[0])).toBe(test[1]);
		});
	}
});

describe("colors.isHsv", () => {
	const tests = [
		[{ h: 0, s: 0, v: 0 }, true],
		[{ h: 0, s: 0, v: 0, a: 1 }, true],
		[{ h: 0, s: 0, a: 1 }, false],
		["hsva( 0, 0, 0)", false]
	];

	for (const test of tests) {
		it(test[0] + " should be " + test[1], () => {
			expect(isHsv(test[0])).toBe(test[1]);
		});
	}
});
