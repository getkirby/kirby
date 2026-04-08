import { beforeEach, describe, expect, it, vi } from "vitest";
import Search from "./search";
import Panel from "./panel.js";

describe("panel.search", () => {
	describe("isLoading", () => {
		it("should be false by default", () => {
			const search = Search({});
			expect(search.isLoading).toStrictEqual(false);
		});
	});

	describe("open()", () => {
		it("should close the menu and open the search dialog", () => {
			const panel = Panel.create(app);
			// @ts-expect-error panel.js is not typed
			const escape = vi.spyOn(panel.menu, "escape");
			// @ts-expect-error panel.js is not typed
			const open = vi.spyOn(panel.dialog, "open").mockResolvedValue({});

			// @ts-expect-error panel.js is not typed
			panel.searcher.open("pages");

			expect(escape).toHaveBeenCalledOnce();
			expect(open).toHaveBeenCalledWith({
				component: "k-search-dialog",
				props: { type: "pages" },
			});
		});
	});

	describe("query()", () => {
		let panel: ReturnType<typeof Panel.create>;

		beforeEach(() => {
			panel = Panel.create(app);
		});

		it("should return null results for short queries", async () => {
			// @ts-expect-error panel.js is not typed
			const result = await panel.searcher.query("pages", "a", {});
			expect(result).toStrictEqual({ results: null, pagination: {} });
		});

		it("should return null results for empty query", async () => {
			// @ts-expect-error panel.js is not typed
			const result = await panel.searcher.query("pages", "", {});
			expect(result).toStrictEqual({ results: null, pagination: {} });
		});

		it("should return search from the API response", async () => {
			const search = {
				results: [{ title: "Home" }],
				pagination: { total: 1 },
			};

			vi.spyOn(panel, "get").mockResolvedValue({ search });

			// @ts-expect-error panel.js is not typed
			const result = await panel.searcher.query("pages", "home", {});
			expect(result).toStrictEqual(search);
		});

		it("should pass query and options to panel.get", async () => {
			const get = vi.spyOn(panel, "get").mockResolvedValue({ search: {} });

			// @ts-expect-error panel.js is not typed
			await panel.searcher.query("pages", "test", { limit: 10, page: 2 });

			expect(get).toHaveBeenCalledWith("/search/pages", {
				query: { query: "test", limit: 10, page: 2 },
				signal: expect.any(AbortSignal),
			});
		});

		it("should return empty results on non-abort error", async () => {
			vi.spyOn(panel, "get").mockRejectedValue(new Error("Server error"));

			// @ts-expect-error panel.js is not typed
			const result = await panel.searcher.query("pages", "test", {});
			expect(result).toStrictEqual({ results: [], pagination: {} });
		});

		it("should return undefined on AbortError", async () => {
			const abortError = Object.assign(new Error("Aborted"), {
				name: "AbortError",
			});
			vi.spyOn(panel, "get").mockRejectedValue(abortError);

			// @ts-expect-error panel.js is not typed
			const result = await panel.searcher.query("pages", "test", {});
			expect(result).toBeUndefined();
		});

		it("should abort a previous request when a new one starts", async () => {
			let firstSignal: AbortSignal | undefined;

			vi.spyOn(panel, "get").mockImplementation(async (_url, options) => {
				if (!firstSignal) {
					firstSignal = (options as { signal?: AbortSignal }).signal;
					// trigger second query before first resolves
					// @ts-expect-error panel.js is not typed
					panel.searcher.query("pages", "second", {});
				}
				return { search: { results: [], pagination: {} } };
			});

			// @ts-expect-error panel.js is not typed
			await panel.searcher.query("pages", "first", {});

			expect(firstSignal?.aborted).toBe(true);
		});
	});
});
