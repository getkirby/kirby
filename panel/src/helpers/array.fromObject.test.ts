import { describe, expect, it } from "vitest";
import { fromObject } from "./array";

describe("$helper.array.fromObject()", () => {
	it("should convert object to array", () => {
		const object = {
			a: "A",
			b: "B"
		};

		expect(fromObject(object)).toEqual(["A", "B"]);
	});

	it("should not alter an existing array", () => {
		const object = ["a", "b"];

		expect(fromObject(object)).toEqual(object);
	});

	it("should return an empty array for null or undefined", () => {
		expect(fromObject(null)).toEqual([]);
		expect(fromObject(undefined)).toEqual([]);
	});
});
