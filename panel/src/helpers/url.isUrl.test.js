/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.isUrl", () => {
	it("should detect URL in string", () => {
		expect(url.isUrl("https://getkirby.com")).toStrictEqual(true);
		expect(url.isUrl("https://getkirby.com", true)).toStrictEqual(true);
		expect(url.isUrl("/foo")).toStrictEqual(true);
	});

	it("should fail on invalid input", () => {
		expect(url.isUrl(false)).toStrictEqual(false);
		expect(url.isUrl({})).toStrictEqual(false);
		expect(url.isUrl(1)).toStrictEqual(false);
		expect(url.isUrl("/foo", true)).toStrictEqual(false);
		expect(url.isUrl("javascript:alert(/XSS/)", true)).toStrictEqual(false);
	});

	it("should detect URL object", () => {
		expect(url.isUrl(new URL("https://getkirby.com"))).toStrictEqual(true);
		expect(url.isUrl(new URL("https://getkirby.com"), true)).toStrictEqual(
			true
		);
		expect(url.isUrl(new URL("javascript:alert(/XSS/)"), true)).toStrictEqual(
			false
		);
	});

	it("should detect Location object", () => {
		expect(url.isUrl(window.location)).toStrictEqual(true);
	});
});
