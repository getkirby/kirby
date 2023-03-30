import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.buildUrl", () => {
	it("should build Url", () => {
		const input = "https://getkirby.com/";
		const result = url.buildUrl(input);

		expect(result.toString()).toStrictEqual(input);
	});

	it("should build Url with query", () => {
		const input = "https://getkirby.com/";
		const result = url.buildUrl(input, {
			search: "test"
		});

		expect(result.toString()).toStrictEqual(input + "?search=test");
	});

	it("should build Url based on origin", () => {
		const origin = "https://getkirby.com/";
		const result = url.buildUrl(
			"/foo",
			{
				search: "test"
			},
			origin
		);

		expect(result.toString()).toStrictEqual(origin + "foo?search=test");
	});
});
