import { describe, expect, it } from "vitest";
import { isReactive, reactive } from "vue";
import { clone } from "./object";

describe("$helper.object.clone()", () => {
	it("should clone an object", () => {
		const original = { a: 1, b: { c: 2 }, c: "C" };
		const copy = clone(original);

		expect(copy).toEqual(original);
		expect(copy).not.toBe(original);
		expect(copy.b).not.toBe(original.b);
	});

	it("should clone an array", () => {
		const original = [1, [2, 3]];
		const copy = clone(original);

		expect(copy).toEqual(original);
		expect(copy).not.toBe(original);
		expect(copy[1]).not.toBe(original[1]);
	});

	it("should return nothing when provided nothing", () => {
		expect(clone()).toEqual(undefined);
	});

	it("should unwrap a reactive object", () => {
		const original = reactive({ a: 1, b: { c: 2 } });

		expect(isReactive(original)).toBe(true);
		expect(isReactive(original.b)).toBe(true);

		const copy = clone(original);

		expect(copy).toEqual({ a: 1, b: { c: 2 } });
		expect(isReactive(copy)).toBe(false);
		expect(isReactive(copy.b)).toBe(false);
	});
});
