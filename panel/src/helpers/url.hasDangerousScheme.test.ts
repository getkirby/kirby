import { describe, expect, it } from "vitest";
import url from "./url";

describe("$helper.url.hasDangerousScheme", () => {
	it.each([
		"javascript:alert(1)",
		"JavaScript:alert(1)",
		"vbscript:msgbox(1)",
		"livescript:alert(1)",
		"mocha:alert(1)",
		"jar:http://evil.com!/",
		"data:text/html,<script>alert(1)</script>"
	])("flags blocked scheme: %s", (value) => {
		expect(url.hasDangerousScheme(value)).toStrictEqual(true);
	});

	it.each([
		"java\nscript:alert(1)",
		" \tjavascript:alert(1)",
		"java script:alert(1)"
	])("flags blocked scheme with whitespace/control chars: %s", (value) => {
		expect(url.hasDangerousScheme(value)).toStrictEqual(true);
	});

	it.each([
		"https://getkirby.com",
		"http://getkirby.com",
		"ftp://getkirby.com",
		"mailto:hello@getkirby.com",
		"tel:+1234567890",
		"/relative/path",
		"#anchor",
		"",
		null,
		undefined
	])("does not flag safe value: %s", (value) => {
		expect(url.hasDangerousScheme(value)).toStrictEqual(false);
	});
});
