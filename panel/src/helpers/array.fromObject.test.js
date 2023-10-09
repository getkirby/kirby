import { describe, expect, it } from "vitest";
import "./array.js";

describe.concurrent("Array.fromObject()", () => {
	it("should convert object to array", () => {
		const object = {
			a: "A",
			b: "B"
		};

		expect(Array.fromObject(object)).toEqual(["A", "B"]);
	});

	it("should not alter an existing array", () => {
		const object = ["a", "b"];

		expect(Array.fromObject(object)).toEqual(object);
	});
});
