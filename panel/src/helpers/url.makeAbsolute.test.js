import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.makeAbsolute", () => {
	it("should not touch absolute URLs", () => {
		const result = url.makeAbsolute("https://getkirby.com");
		expect(result).toStrictEqual("https://getkirby.com");
	});
});
