import { describe, expect, it } from "vitest";
import { sortBy } from "./array";

describe("$helper.array.sortBy()", () => {
	it("should sort ascending by default and case-insensitively", () => {
		const array = [{ name: "Banana" }, { name: "apple" }, { name: "Cherry" }];

		expect(sortBy(array, "name")).toEqual([
			{ name: "apple" },
			{ name: "Banana" },
			{ name: "Cherry" }
		]);
	});

	it("should sort descending", () => {
		const array = [{ name: "apple" }, { name: "Banana" }, { name: "Cherry" }];

		expect(sortBy(array, "name desc")).toEqual([
			{ name: "Cherry" },
			{ name: "Banana" },
			{ name: "apple" }
		]);
	});

	it("should sort numbers naturally", () => {
		const array = [{ n: "item10" }, { n: "item2" }, { n: "item1" }];

		expect(sortBy(array, "n")).toEqual([
			{ n: "item1" },
			{ n: "item2" },
			{ n: "item10" }
		]);
	});

	it("should treat a missing field value as empty", () => {
		const array = [{ name: "b" }, { foo: "bar" }, { name: "a" }];

		expect(sortBy(array, "name")).toEqual([
			{ foo: "bar" },
			{ name: "a" },
			{ name: "b" }
		]);
	});
});
