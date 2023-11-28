import { describe, expect, it } from "vitest";
import object from "./object.js";

describe("$helper.object.merge", () => {
	it("should count object props", () => {
		const result = object.length({
			a: "a",
			b: "b",
			c: "c"
		});

		expect(result).toStrictEqual(3);
	});

	it("should work with empty objects", () => {
		const result = object.length({});
		expect(result).toStrictEqual(0);
	});

	it("should work with undefined objects", () => {
		const result = object.length();
		expect(result).toStrictEqual(0);
	});

	it("should also work with arrays", () => {
		const result = object.length(["a", "b", "c"]);

		expect(result).toStrictEqual(3);
	});
});
