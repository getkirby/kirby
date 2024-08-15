import { describe, expect, it } from "vitest";
import { wrap } from "./array.js";

describe.concurrent("array.wrap()", () => {
	it("should wrap in an array", () => {
		expect(wrap("|")).toEqual(["|"]);
		expect(wrap(["|"])).toEqual(["|"]);
	});
});
