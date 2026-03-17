import { describe, expect, it, vi } from "vitest";
import url from "./url";

describe("$helper.url.isSameOrigin", () => {
	vi.stubGlobal("location", new URL("https://test.com"));

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
