import { describe, expect, it } from "vitest";
import { same } from "./object.js";

describe("$helper.object.same", () => {
	it("should return true for identical objects", () => {
		const a = { a: 1, b: { c: 2 } };
		const b = { a: 1, b: { c: 2 } };
		expect(same(a, b)).toBe(true);
	});

	it("should returns false for different objects", () => {
		const a = { a: 1, b: { c: 2 } };
		const b = { a: 1, b: { c: 3 } };
		expect(same(a, b)).toBe(false);
	});

	it("should returns true for arrays with identical elements", () => {
		const a = [1, { a: 2 }];
		const b = [1, { a: 2 }];
		expect(same(a, b)).toBe(true);
	});

	it("should returns false for arrays with different elements", () => {
		const a = [1, { a: 2 }];
		const b = [1, { a: 3 }];
		expect(same(a, b)).toBe(false);
	});
});
