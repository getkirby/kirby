import { describe, expect, it } from "vitest";
import { toLowerKeys } from "./object.js";

describe("$helper.object.toLowerKeys", () => {
	it("should convert all keys to lowercase", () => {
		const obj = { A: 1, b: 2, C: 3 };
		const result = toLowerKeys(obj);

		expect(result).toEqual({ a: 1, b: 2, c: 3 });
		expect(result).not.toBe(obj);
	});

	it("should return empty object if input is empty", () => {
		expect(toLowerKeys({})).toEqual({});
	});
});
