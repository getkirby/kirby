import { describe, expect, it } from "vitest";
import "./array.js";

describe.concurrent("Array.wrap()", () => {
	it("should wrap in an array", () => {
		expect(Array.wrap("|")).toEqual(["|"]);
		expect(Array.wrap(["|"])).toEqual(["|"]);
	});
});
