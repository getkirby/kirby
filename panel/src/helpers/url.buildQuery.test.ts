import { describe, expect, it } from "vitest";
import url from "./url";

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

	it("should remove null from origin", () => {
		const query = url.buildQuery(
			{
				search: null
			},
			"?page=1&search=Test"
		);

		expect(query.toString()).toStrictEqual("page=1");
	});

	it("should nest objects", () => {
		const query = url.buildQuery({
			fields: {
				gallery: { page: 2, searchterm: "Test" }
			}
		});

		expect(decodeURIComponent(query.toString())).toStrictEqual(
			"fields[gallery][page]=2&fields[gallery][searchterm]=Test"
		);
	});

	it("should only replace the addressed scope", () => {
		const query = url.buildQuery(
			{
				fields: { gallery: { page: 2 } }
			},
			"?fields%5Bcover%5D%5Bpage%5D=3&fields%5Bgallery%5D%5Bpage%5D=1"
		);

		expect(decodeURIComponent(query.toString())).toStrictEqual(
			"fields[cover][page]=3&fields[gallery][page]=2"
		);
	});

	it("should remove a nested null", () => {
		const query = url.buildQuery(
			{
				fields: { gallery: { searchterm: null } }
			},
			"?fields%5Bgallery%5D%5Bpage%5D=1&fields%5Bgallery%5D%5Bsearchterm%5D=Test"
		);

		expect(decodeURIComponent(query.toString())).toStrictEqual(
			"fields[gallery][page]=1"
		);
	});

	it("should skip undefined", () => {
		const query = url.buildQuery({
			search: "Test",
			page: undefined
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
