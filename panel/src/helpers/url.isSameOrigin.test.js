/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.isSameOrigin", () => {
	window.location = new URL("https://test.com");

	it("should detect same origin", () => {
		const input = "https://test.com";
		const result = url.isSameOrigin(input);

		expect(result).toStrictEqual(true);
	});

	it("should detect different origin", () => {
		const input = "https://getkirby.com";
		const result = url.isSameOrigin(input);

		expect(result).toStrictEqual(false);
	});
});
