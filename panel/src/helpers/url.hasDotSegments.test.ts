import { describe, expect, it } from "vitest";
import url from "./url";

describe("$helper.url.hasDotSegments", () => {
	it.each([
		"../api/pages/a",
		"shop/sync/../../../api",
		"shop/./sync",
		"shop/sync/..",
		"shop/%2e%2E/sync",
		"shop/.%2e/sync",
		"shop\\..\\sync",
		"shop/.\t./sync",
		"shop/.\n./sync",
		"https://example.com/a/../b"
	])("flags dot segments: %j", (value) => {
		expect(url.hasDotSegments(value)).toStrictEqual(true);
	});

	it.each([
		"shop/sync",
		"shop/sync/a.json",
		"shop/..sync/a",
		"shop/sync../a",
		"shop/sync?path=../api",
		"shop/sync#../api",
		"https://example.com/a/b"
	])("ignores other dots: %j", (value) => {
		expect(url.hasDotSegments(value)).toStrictEqual(false);
	});
});
