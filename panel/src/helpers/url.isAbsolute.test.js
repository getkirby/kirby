import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.isAbsolute", () => {
	it("should work", () => {
		expect(url.isAbsolute("https://getkirby.com")).toStrictEqual(true);
		expect(url.isAbsolute("http://getkirby.com")).toStrictEqual(true);
		expect(url.isAbsolute("/foo")).toStrictEqual(false);
	});
});
