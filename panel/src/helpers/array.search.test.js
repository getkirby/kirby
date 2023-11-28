import { describe, expect, it } from "vitest";
import { search } from "./array";

describe.concurrent("$helper.array.search()", () => {
	const array = [
		{ text: "apple", code: "AP" },
		{ text: "banana", code: "BA" },
		{ text: "orange", code: "OR" },
		{ text: "grape", code: "GR" }
	];

	it("should return original array if query is empty", () => {
		const result = search(array, "");
		expect(result).toEqual(array);
	});

	it("should return original array if query is too short", () => {
		const result = search(array, "a", { min: 2 });
		expect(result).toEqual(array);
	});

	it("should filter array with the default text field", () => {
		const result = search(array, "an");
		expect(result).toEqual([
			{ text: "banana", code: "BA" },
			{ text: "orange", code: "OR" }
		]);
	});

	it("should filter array with a custom field", () => {
		const result = search(array, "AP", { field: "code" });
		expect(result).toEqual([{ text: "apple", code: "AP" }]);
	});

	it("should filter array case-insensitively", () => {
		const result = search(array, "Or");
		expect(result).toEqual([{ text: "orange", code: "OR" }]);
	});
});
