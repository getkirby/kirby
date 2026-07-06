import { describe, expect, it } from "vitest";
import { length } from "./object";
import {
	detect,
	getFileUUID,
	getPageUUID,
	isFileUUID,
	isPageUUID,
	preview,
	types
} from "./link";

// mock $t() function
// @ts-expect-error - window.panel has no type yet
window.panel = {
	$t: (value: string) => value
};

describe("$helper.link.detect()", () => {
	it("should detect page UUID", () => {
		expect(detect("page://324hjk24")!.type).toStrictEqual("page");
	});
	it("should detect file UUID", () => {
		expect(detect("file://324hjk24")!.type).toStrictEqual("file");
	});
	it("should detect absolute URL", () => {
		expect(detect("http://getkirby.com")!.type).toStrictEqual("url");
		expect(detect("https://getkirby.com")!.type).toStrictEqual("url");
	});
	it("should detect email", () => {
		expect(detect("mailto:test@getkirby.com")!.type).toStrictEqual("email");
	});
	it("should detect tel", () => {
		expect(detect("tel:12345678")!.type).toStrictEqual("tel");
	});
	it("should detect anchor", () => {
		expect(detect("#header")!.type).toStrictEqual("anchor");
	});
	it("should detect custom", () => {
		expect(detect("foo-bar")!.type).toStrictEqual("custom");
	});

	it("should detect empty as url", () => {
		expect(detect("")!.type).toStrictEqual("url");
	});
});

describe("$helper.link.getFileUUID()", () => {
	it("should return UUID from permalink", () => {
		expect(getFileUUID("/@/file/324hjk24")).toStrictEqual("file://324hjk24");
	});
});

describe("$helper.link.getPageUUID()", () => {
	it("should return UUID from permalink", () => {
		expect(getPageUUID("/@/page/324hjk24")).toStrictEqual("page://324hjk24");
		expect(getPageUUID("/en/@/page/324hjk24")).toStrictEqual("page://324hjk24");
		expect(getPageUUID("/de/@/page/324hjk24")).toStrictEqual("page://324hjk24");
	});
});

describe("$helper.link.isFileUUID()", () => {
	it("should detect UUID", () => {
		expect(isFileUUID("file://324hjk24")).toBeTruthy();
		expect(isFileUUID("/@/file/324hjk24")).toBeTruthy();
	});
});

describe("$helper.link.isPageUUID()", () => {
	it("should detect UUID", () => {
		expect(isPageUUID("page://324hjk24")).toBeTruthy();
		expect(isPageUUID("/@/page/324hjk24")).toBeTruthy();
		expect(isPageUUID("/en/@/page/324hjk24")).toBeTruthy();
		expect(isPageUUID("site://")).toBeTruthy();
	});
});

describe("$helper.link.preview()", () => {
	it("should request default page preview fields", async () => {
		const calls: Array<{ id: string; query: { select: string } }> = [];

		// @ts-expect-error - window.panel has no type yet
		window.panel.api = {
			pages: {
				get(id: string, query: { select: string }) {
					calls.push({ id, query });
					return {
						panelImage: { icon: "page" },
						title: "Page"
					};
				}
			}
		};

		await preview({ type: "page", link: "page://324hjk24" });

		expect(calls[0]).toStrictEqual({
			id: "page://324hjk24",
			query: { select: "title,panelImage" }
		});
	});

	it("should request default file preview fields", async () => {
		const calls: Array<{ id: string; query: { select: string } }> = [];

		// @ts-expect-error - window.panel has no type yet
		window.panel.api = {
			files: {
				get(parent: string | null, id: string, query: { select: string }) {
					calls.push({ id, query });
					return {
						filename: "image.jpg",
						panelImage: { icon: "image" }
					};
				}
			}
		};

		await preview({ type: "file", link: "file://324hjk24" });

		expect(calls[0]).toStrictEqual({
			id: "file://324hjk24",
			query: { select: "filename,panelImage" }
		});
	});
});

describe("$helper.link.types()", () => {
	it("should return all types", () => {
		expect(length(types())).toStrictEqual(7);
	});
	it("should return active types", () => {
		expect(length(types(["page", "file", "url"]))).toStrictEqual(3);
	});
});
