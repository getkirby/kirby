import { describe, expect, it } from "vitest";
import minMax from "./minMax";

describe("minMax", () => {
	it("returns the value when it is within the range", () => {
		expect(minMax(5, 0, 10)).toBe(5);
	});

	it("returns min when the value is below the range", () => {
		expect(minMax(-5, 0, 10)).toBe(0);
	});

	it("returns max when the value is above the range", () => {
		expect(minMax(15, 0, 10)).toBe(10);
	});

	it("returns min when the value equals min", () => {
		expect(minMax(0, 0, 10)).toBe(0);
	});

	it("returns max when the value equals max", () => {
		expect(minMax(10, 0, 10)).toBe(10);
	});

	it("works with negative ranges", () => {
		expect(minMax(-3, -10, -1)).toBe(-3);
		expect(minMax(-15, -10, -1)).toBe(-10);
		expect(minMax(0, -10, -1)).toBe(-1);
	});

	it("returns 0 when called with no arguments", () => {
		expect(minMax()).toBe(0);
	});
});
