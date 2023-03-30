import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.isSameOrigin", () => {
	it("should detect same origin", () => {
		const input = "http://localhost:3000";
		const result = url.isSameOrigin(input);

		expect(result).toStrictEqual(true);
	});

	it("should detect different origin", () => {
		const input = "https://getkirby.com";
		const result = url.isSameOrigin(input);

		expect(result).toStrictEqual(false);
	});
});
