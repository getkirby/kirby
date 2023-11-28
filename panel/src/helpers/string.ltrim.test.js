import { describe, expect, it } from "vitest";
import string from "./string.js";

describe("$helper.string.ltrim", () => {
	it("should trim the character", () => {
		const result = string.ltrim("/foo", "/");
		expect(result).toStrictEqual("foo");
	});

	it("should trim multiple characters", () => {
		const result = string.ltrim("//foo", "/");
		expect(result).toStrictEqual("foo");
	});
});
