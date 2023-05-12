import { describe, expect, it } from "vitest";
import string from "./string.js";

describe("$helper.string.rtrim", () => {
	it("should trim the character", () => {
		const result = string.rtrim("foo/", "/");
		expect(result).toStrictEqual("foo");
	});

	it("should trim multiple characters", () => {
		const result = string.rtrim("foo//", "/");
		expect(result).toStrictEqual("foo");
	});
});
