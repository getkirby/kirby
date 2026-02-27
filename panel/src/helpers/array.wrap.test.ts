import { describe, expect, it } from "vitest";
import { wrap } from "./array";

describe.concurrent("$helper.array.wrap()", () => {
	it("should wrap in an array", () => {
		expect(wrap("|")).toEqual(["|"]);
		expect(wrap(["|"])).toEqual(["|"]);
	});
});
