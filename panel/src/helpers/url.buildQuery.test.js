import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.buildQuery", () => {
	it("should build query", () => {
		const query = url.buildQuery({
			search: "Test"
		});

		expect(query.toString()).toStrictEqual("search=Test");
	});

	it("should skip null", () => {
		const query = url.buildQuery({
			search: "Test",
			page: null
		});

		expect(query.toString()).toStrictEqual("search=Test");
	});

	it("should keep values from origin", () => {
		const origin = "?page=1";

		const query = url.buildQuery(
			{
				search: "Test"
			},
			origin
		);

		expect(query.toString()).toStrictEqual("page=1&search=Test");
	});

	it("should keep values from URL origin", () => {
		const origin = new URL("https://getkirby.com/?page=1");

		const query = url.buildQuery(
			{
				search: "Test"
			},
			origin
		);

		expect(query.toString()).toStrictEqual("page=1&search=Test");
	});
});
