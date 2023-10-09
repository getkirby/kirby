import { describe, expect, it } from "vitest";
import { isObject } from "./object";

describe.concurrent("isObject", () => {
	it("returns true for an object", () => {
		expect(isObject({})).toBe(true);
		expect(isObject({ a: 1, b: 2 })).toBe(true);
		expect(isObject(new Object())).toBe(true);
	});

	it("returns false for a non-object", () => {
		expect(isObject(undefined)).toBe(false);
		expect(isObject(null)).toBe(false);
		expect(isObject(42)).toBe(false);
		expect(isObject("hello")).toBe(false);
		expect(isObject([])).toBe(false);
		expect(isObject(function () {})).toBe(false);
	});
});
