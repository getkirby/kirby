import { describe, expect, it } from "vitest";
import type Api from "./index";
import pages from "./pages";

const api = pages({} as Api);

describe("api.pages.id()", () => {
	it("should convert page permalink", () => {
		expect(api.id("/@/page/D1yCxHPlHzgzBJI5")).toBe("@D1yCxHPlHzgzBJI5");
	});

	it("should convert language-prefixed page permalink", () => {
		expect(api.id("/de/@/page/D1yCxHPlHzgzBJI5")).toBe("@D1yCxHPlHzgzBJI5");
	});

	it("should convert page UUID", () => {
		expect(api.id("page://D1yCxHPlHzgzBJI5")).toBe("@D1yCxHPlHzgzBJI5");
	});

	it("should escape slashes in a plain id", () => {
		expect(api.id("blog/article")).toBe("blog+article");
	});
});
