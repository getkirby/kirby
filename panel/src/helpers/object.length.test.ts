import { describe, expect, it } from "vitest";
import { length } from "./object";

describe("$helper.object.length()", () => {
	it("should count object props", () => {
		const result = length({
			a: "a",
			b: "b",
			c: "c"
		});

		expect(result).toStrictEqual(3);
	});

	it("should work with empty objects", () => {
		const result = length({});
		expect(result).toStrictEqual(0);
	});

	it("should work with undefined objects", () => {
		const result = length();
		expect(result).toStrictEqual(0);
	});

	it("should also work with arrays", () => {
		const result = length(["a", "b", "c"]);

		expect(result).toStrictEqual(3);
	});
});
