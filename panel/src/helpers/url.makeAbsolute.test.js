/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.makeAbsolute", () => {
	it("should not touch absolute URLs", () => {
		const result = url.makeAbsolute("https://getkirby.com");
		expect(result).toStrictEqual("https://getkirby.com");
	});

	it("should make URLs absolute", () => {
		window.location = new URL("https://getkirby.com");

		const result = url.makeAbsolute("/foo");
		expect(result).toStrictEqual("https://getkirby.com/foo");
	});
});
