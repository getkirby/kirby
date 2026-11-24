import { describe, expect, it } from "vitest";
import { length } from "./object";
import {
	detect,
	getFileUUID,
	getPageUUID,
	isFileUUID,
	isPageUUID,
	types
} from "./link";

describe("$helper.link", () => {
	describe("detect()", () => {
		it.each([
			{ input: "page://324hjk24", type: "page" },
			{ input: "file://324hjk24", type: "file" },
			{ input: "http://getkirby.com", type: "url" },
			{ input: "https://getkirby.com", type: "url" },
			{ input: "mailto:test@getkirby.com", type: "email" },
			{ input: "tel:12345678", type: "tel" },
			{ input: "#header", type: "anchor" },
			{ input: "foo-bar", type: "custom" }
		])("should detect $input as $type", ({ input, type }) => {
			expect(detect(input)!.type).toStrictEqual(type);
		});

		it("should detect empty as url", () => {
			expect(detect("")!.type).toStrictEqual("url");
		});

		it("should fall back to url type when custom types are empty", () => {
			expect(detect("", {})!.type).toStrictEqual("url");
		});
	});

	describe("getFileUUID()", () => {
		it("should return UUID from permalink", () => {
			expect(getFileUUID("/@/file/324hjk24")).toStrictEqual("file://324hjk24");
		});
	});

	describe("getPageUUID()", () => {
		it("should return UUID from permalink", () => {
			expect(getPageUUID("/@/page/324hjk24")).toStrictEqual("page://324hjk24");
			expect(getPageUUID("/en/@/page/324hjk24")).toStrictEqual(
				"page://324hjk24"
			);
			expect(getPageUUID("/de/@/page/324hjk24")).toStrictEqual(
				"page://324hjk24"
			);
		});
	});

	describe("isFileUUID()", () => {
		it("should detect UUID", () => {
			expect(isFileUUID("file://324hjk24")).toBeTruthy();
			expect(isFileUUID("/@/file/324hjk24")).toBeTruthy();
		});
	});

	describe("isPageUUID()", () => {
		it("should detect UUID", () => {
			expect(isPageUUID("page://324hjk24")).toBeTruthy();
			expect(isPageUUID("/@/page/324hjk24")).toBeTruthy();
			expect(isPageUUID("/en/@/page/324hjk24")).toBeTruthy();
			expect(isPageUUID("site://")).toBeTruthy();
		});
	});

	describe("types()", () => {
		it("should return all types", () => {
			expect(length(types())).toStrictEqual(7);
		});
		it("should return active types", () => {
			expect(length(types(["page", "file", "url"]))).toStrictEqual(3);
		});

		it("should ignore unknown keys", () => {
			expect(length(types(["page", "does-not-exist"]))).toStrictEqual(1);
		});

		it("should pass through the value for plain types", () => {
			const type = types();
			expect(type.url.value("x")).toBe("x");
			expect(type.page.value("x")).toBe("x");
			expect(type.file.value("x")).toBe("x");
			expect(type.anchor.value("x")).toBe("x");
			expect(type.custom.value("x")).toBe("x");
		});

		it("should prefix mailto: for email values", () => {
			expect(types().email.value("test@getkirby.com")).toBe(
				"mailto:test@getkirby.com"
			);
		});

		it("should prefix tel: for tel values", () => {
			expect(types().tel.value("12345678")).toBe("tel:12345678");
		});
	});
});
