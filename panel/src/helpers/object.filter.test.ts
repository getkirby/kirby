import { describe, expect, it } from "vitest";
import { filter } from "./object";

describe("$helper.object.filter()", () => {
	it("should filter by value", () => {
		const obj = { a: 1, b: 2, c: 3 };
		const predicate = (value: unknown) => (value as number) > 1;
		const expected = { b: 2, c: 3 };
		expect(filter(obj, predicate)).toEqual(expected);
	});

	it("should filter by key", () => {
		const obj = { a: 1, b: 2 };
		const predicate = (_value: unknown, key: string) => key === "a";
		const expected = { a: 1 };

		expect(filter(obj, predicate)).toEqual(expected);
	});

	it("should return an empty object when nothing matches", () => {
		const obj = { a: 1 };
		const predicate = () => false;
		const expected = {};

		expect(filter(obj, predicate)).toEqual(expected);
	});
});
