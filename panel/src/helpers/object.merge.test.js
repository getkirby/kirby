import { describe, expect, it } from "vitest";
import { merge } from "./object.js";

describe("$helper.object.merge", () => {
	it("should merge two objects", () => {
		const target = {
			nested: {
				a: "a"
			}
		};

		const source = {
			nested: {
				b: "b"
			}
		};

		const expected = {
			nested: {
				a: "a",
				b: "b"
			}
		};

		const result = merge(target, source);

		expect(result).toEqual(expected);
	});

	it("should return the target object if source is undefined", () => {
		const target = { a: 1 };
		const result = merge(target, undefined);

		expect(result).toBe(target);
	});
});
