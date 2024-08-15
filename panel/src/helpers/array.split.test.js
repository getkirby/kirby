import { describe, expect, it } from "vitest";
import { split } from "./array.js";

describe.concurrent("Array.split()", () => {
	it("should split array into groups", () => {
		const array = ["a", "b", "|", "c", "d", "|", "e", "f"];
		const expected = [
			["a", "b"],
			["c", "d"],
			["e", "f"]
		];

		expect(split(array, "|")).toEqual(expected);
	});
});
