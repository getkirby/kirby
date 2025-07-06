/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.isUrl", () => {
	it("should detect URL in string", () => {
		// URL tests from our backend code in VTest.php
		const validUrls = [
			"http://www.getkirby.com",
			"http://www.getkirby.com/docs/param:value/?foo=bar/#anchor",
			"https://www.getkirby.de.vu",
			"https://getkirby.com:1234",
			"https://getkirby.com:1234/test",
			"http://foo.com/blah_blah",
			"http://foo.com/blah_blah/",
			"http://foo.com/blah_blah_(wikipedia)",
			"http://foo.com/blah_blah_(wikipedia)_(again)",
			"http://www.example.com/wpstyle/?p=364",
			"https://www.example.com/foo/?bar=baz&inga=42&quux",
			"http://✪df.ws/123",
			"http://userid:password@example.com:8080",
			"http://userid:password@example.com:8080/",
			"http://userid@example.com",
			"http://userid@example.com/",
			"http://userid@example.com:8080",
			"http://userid@example.com:8080/",
			"http://userid:password@example.com",
			"http://userid:password@example.com/",
			"http://142.42.1.1/",
			"http://142.42.1.1:8080/",
			"http://➡.ws/䨹",
			"http://⌘.ws",
			"http://⌘.ws/",
			"http://foo.com/blah_(wikipedia)#cite-1",
			"http://foo.com/blah_(wikipedia)_blah#cite-1",
			"http://foo.com/unicode_(✪)_in_parens",
			"http://foo.com/(something)?after=parens",
			"http://☺.damowmow.com/",
			"http://code.google.com/events/#&product=browser",
			"http://j.mp",
			"ftp://foo.bar/baz",
			"http://foo.bar/?q=Test%20URL-encoded%20stuff",
			"http://مثال.إختبار",
			"http://例子.测试",
			"http://उदाहरण.परीक्षा",
			"http://-.~_!$&'()*+,;=:%40:80%2f::::::@example.com",
			"http://1337.net",
			"http://a.b-c.de",
			"http://223.255.255.254",
			"http://localhost/test/",
			"http://localhost:8080/test",
			"http://127.0.0.1/kirby/",
			"http://127.0.0.1:8080/kirby",
			"https://127.0.0.1/kirby/panel/pages/blog+vvvv",
			"https://localhost/kirby/panel/pages/blog+vvvv"
		];

		validUrls.forEach((testUrl) => {
			expect(url.isUrl(testUrl, true)).toStrictEqual(true);
		});

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
