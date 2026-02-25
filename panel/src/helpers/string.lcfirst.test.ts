import { describe, expect, it } from "vitest";
import string from "./string";

describe.concurrent("$helper.string.lcfirst", () => {
	it("should convert first character to lowercase", () => {
		const result = string.lcfirst("Hello");
		expect(result).toBe("hello");
	});

	it("should convert single character to lowercase", () => {
		const result = string.lcfirst("H");
		expect(result).toBe("h");
	});
});
