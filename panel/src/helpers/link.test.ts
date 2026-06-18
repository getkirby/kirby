import { beforeEach, describe, expect, it, vi } from "vitest";
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

window.panel = {
	t: (value: string) => value
} as unknown as typeof window.panel;

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

	describe("preview()", () => {
		beforeEach(() => {
			window.panel = {
				t: (value: string) => value,
				api: {
					files: { get: vi.fn() },
					pages: { get: vi.fn() }
				}
			} as unknown as typeof window.panel;
		});

		it("should return null when there is no link", async () => {
			expect(await preview({ type: "url", link: "" })).toBeNull();
		});

		it("should return a label-only preview for a plain link", async () => {
			expect(
				await preview({ type: "url", link: "https://getkirby.com" })
			).toEqual({
				label: "https://getkirby.com"
			});
		});

		it("should return a page preview", async () => {
			vi.mocked(window.panel.api.pages.get).mockResolvedValue({
				title: "About",
				panelImage: "IMG"
			});

			const result = await preview({ type: "page", link: "page://2" }, [
				"title",
				"panelImage"
			]);

			expect(result).toEqual({ label: "About", image: "IMG" });
			expect(window.panel.api.pages.get).toHaveBeenCalledWith("page://2", {
				select: "title,panelImage"
			});
		});

		it("should return the site label for site://", async () => {
			const result = await preview({ type: "page", link: "site://" });

			expect(result).toEqual({ label: "view.site" });
			expect(window.panel.api.pages.get).not.toHaveBeenCalled();
		});

		it("should return null when the page api throws", async () => {
			vi.mocked(window.panel.api.pages.get).mockRejectedValue(
				new Error("nope")
			);
			expect(await preview({ type: "page", link: "page://2" })).toBeNull();
		});

		it("should return a file preview", async () => {
			vi.mocked(window.panel.api.files.get).mockResolvedValue({
				filename: "image.png",
				panelImage: "IMG"
			});

			const result = await preview({ type: "file", link: "file://1" }, [
				"filename",
				"panelImage"
			]);

			expect(result).toEqual({ label: "image.png", image: "IMG" });
			expect(window.panel.api.files.get).toHaveBeenCalledWith(
				null,
				"file://1",
				{
					select: "filename,panelImage"
				}
			);
		});

		it("should return null when the file api throws", async () => {
			vi.mocked(window.panel.api.files.get).mockRejectedValue(
				new Error("nope")
			);
			expect(await preview({ type: "file", link: "file://1" })).toBeNull();
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
