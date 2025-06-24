import { describe, expect, it } from "vitest";
import { fromObject } from "./array.js";

describe.concurrent("array.fromObject()", () => {
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
});
