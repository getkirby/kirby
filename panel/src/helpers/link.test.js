/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import { length } from "./object.js";
import link from "./link.js";

// mock $t() function
window.panel = {
	$t: (value) => value
};

describe("$helper.link.detect()", () => {
	it("should detect page UUID", () => {
		expect(link.detect("page://324hjk24").type).toStrictEqual("page");
	});
	it("should detect file UUID", () => {
		expect(link.detect("file://324hjk24").type).toStrictEqual("file");
	});
	it("should detect absolute URL", () => {
		expect(link.detect("http://getkirby.com").type).toStrictEqual("url");
		expect(link.detect("https://getkirby.com").type).toStrictEqual("url");
	});
	it("should detect email", () => {
		expect(link.detect("mailto:test@getkirby.com").type).toStrictEqual("email");
	});
	it("should detect tel", () => {
		expect(link.detect("tel:12345678").type).toStrictEqual("tel");
	});
	it("should detect anchor", () => {
		expect(link.detect("#header").type).toStrictEqual("anchor");
	});
	it("should detect custom", () => {
		expect(link.detect("foo-bar").type).toStrictEqual("custom");
	});

	it("should detect empty as url", () => {
		expect(link.detect("").type).toStrictEqual("url");
	});
});

describe("$helper.link.getFileUUID()", () => {
	it("should return UUID from permalink", () => {
		expect(link.getFileUUID("/@/file/324hjk24")).toStrictEqual(
			"file://324hjk24"
		);
	});
});

describe("$helper.link.getPageUUID()", () => {
	it("should return UUID from permalink", () => {
		expect(link.getPageUUID("/@/page/324hjk24")).toStrictEqual(
			"page://324hjk24"
		);
		expect(link.getPageUUID("/en/@/page/324hjk24")).toStrictEqual(
			"page://324hjk24"
		);
		expect(link.getPageUUID("/de/@/page/324hjk24")).toStrictEqual(
			"page://324hjk24"
		);
	});
});

describe("$helper.link.isFileUUID()", () => {
	it("should detect UUID", () => {
		expect(link.isFileUUID("file://324hjk24")).toBeTruthy();
		expect(link.isFileUUID("/@/file/324hjk24")).toBeTruthy();
	});
});

describe("$helper.link.isPageUUID()", () => {
	it("should detect UUID", () => {
		expect(link.isPageUUID("page://324hjk24")).toBeTruthy();
		expect(link.isPageUUID("/@/page/324hjk24")).toBeTruthy();
		expect(link.isPageUUID("/en/@/page/324hjk24")).toBeTruthy();
		expect(link.isPageUUID("site://")).toBeTruthy();
	});
});

describe("$helper.link.types()", () => {
	it("should return all types", () => {
		expect(length(link.types())).toStrictEqual(7);
	});
	it("should return active types", () => {
		expect(length(link.types(["page", "file", "url"]))).toStrictEqual(3);
	});
});
