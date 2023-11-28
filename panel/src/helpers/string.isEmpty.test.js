import { describe, expect, it } from "vitest";
import string from "./string.js";

describe.concurrent("$helper.string.isEmpty", () => {
	it("should work with null", () => {
		const result = string.isEmpty(null);
		expect(result).toStrictEqual(true);
	});

	it("should work with undefined", () => {
		const result = string.isEmpty();
		expect(result).toStrictEqual(true);
	});

	it("should work with false", () => {
		const result = string.isEmpty(false);
		expect(result).toStrictEqual(true);
	});

	it("should work with empty string", () => {
		const result = string.isEmpty("");
		expect(result).toStrictEqual(true);
	});

	it("should work with non-empty string", () => {
		const result = string.isEmpty("Hello");
		expect(result).toStrictEqual(false);
	});
});
