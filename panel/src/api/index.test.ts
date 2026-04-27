import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import Api from "./index";

vi.mock("@/panel/request", () => ({ request: vi.fn() }));
import { request as mockRequest } from "@/panel/request";

function makePanel(overrides: Record<string, unknown> = {}) {
	return {
		system: { csrf: "test-csrf" },
		urls: { api: "https://example.com/api/" },
		config: {},
		language: { code: "en" },
		isOffline: false,
		isLoading: false,
		...overrides
	};
}

function makeResponse(json: Record<string, unknown>) {
	return {
		request: new Request("https://example.com/api"),
		response: {
			headers: new Headers(),
			json,
			ok: true,
			status: 200,
			statusText: "OK",
			text: JSON.stringify(json),
			url: "https://example.com/api"
		}
	};
}

describe("api", () => {
	beforeEach(() => {
		vi.useFakeTimers();
		vi.mocked(mockRequest).mockResolvedValue(makeResponse({ result: "ok" }));
	});

	afterEach(() => {
		vi.useRealTimers();
		vi.clearAllMocks();
	});

	describe("properties", () => {
		it("should read defaults from panel", () => {
			const api = new Api(
				makePanel({
					config: { api: { methodOverride: true } },
					language: { code: "de" }
				})
			);

			expect(api.csrf).toStrictEqual("test-csrf");
			expect(api.language).toStrictEqual("de");
			expect(api.methodOverride).toStrictEqual(true);
		});

		it("should strip trailing slash from endpoint", () => {
			const api = new Api(
				makePanel({ urls: { api: "https://example.com/api/" } })
			);
			expect(api.endpoint).toStrictEqual("https://example.com/api");
		});

		it("should default methodOverride to false when not configured", () => {
			const api = new Api(makePanel({ config: {} }));
			expect(api.methodOverride).toStrictEqual(false);
		});
	});

	describe("delete()", () => {
		it("should delegate to post with DELETE method", async () => {
			const api = new Api(makePanel());
			await api.delete("pages/test");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.objectContaining({ method: "DELETE" })
			);
		});
	});

	describe("get()", () => {
		it("should call request with GET method", async () => {
			const api = new Api(makePanel());
			await api.get("pages");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({ method: "GET" })
			);
		});

		it("should append query params to path", async () => {
			const api = new Api(makePanel());
			await api.get("pages", { search: "test" });
			const [url] = vi.mocked(mockRequest).mock.calls[0];
			expect(url).toContain("search=test");
		});

		it("should not append a query string for empty query object", async () => {
			const api = new Api(makePanel());
			await api.get("pages", {});
			const [url] = vi.mocked(mockRequest).mock.calls[0];
			expect(url).not.toContain("?");
		});
	});

	describe("patch()", () => {
		it("should delegate to post with PATCH method", async () => {
			const api = new Api(makePanel());
			await api.patch("pages/test", { title: "Updated" });
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.objectContaining({
					method: "PATCH",
					body: JSON.stringify({ title: "Updated" })
				})
			);
		});
	});

	describe("ping()", () => {
		it("should call auth.ping after 5 minutes when online", () => {
			const api = new Api(makePanel({ isOffline: false }));
			const spy = vi.spyOn(api.auth, "ping").mockResolvedValue(undefined);

			vi.advanceTimersByTime(5 * 60 * 1000);

			expect(spy).toHaveBeenCalledOnce();
		});

		it("should not call auth.ping when offline", () => {
			const api = new Api(makePanel({ isOffline: true }));
			const spy = vi.spyOn(api.auth, "ping").mockResolvedValue(undefined);

			vi.advanceTimersByTime(5 * 60 * 1000);

			expect(spy).not.toHaveBeenCalled();
		});

		it("should replace the previous interval when called again", () => {
			const api = new Api(makePanel({ isOffline: false }));
			const spy = vi.spyOn(api.auth, "ping").mockResolvedValue(undefined);

			const previousId = api.pingId;
			api.ping();

			expect(api.pingId).not.toStrictEqual(previousId);

			vi.advanceTimersByTime(5 * 60 * 1000);
			expect(spy).toHaveBeenCalledOnce();
		});
	});

	describe("post()", () => {
		it("should call request with POST method and serialized body", async () => {
			const api = new Api(makePanel());
			await api.post("pages", { title: "Test" });
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({
					method: "POST",
					body: JSON.stringify({ title: "Test" })
				})
			);
		});
	});

	describe("request()", () => {
		it("should construct URL from endpoint and path", async () => {
			const api = new Api(makePanel());
			await api.get("pages/test");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.anything()
			);
		});

		it("should strip leading slash from path", async () => {
			const api = new Api(makePanel());
			await api.get("/pages/test");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.anything()
			);
		});

		it("should set panel.isLoading to true during a request", async () => {
			const panel = makePanel();
			const api = new Api(panel);
			const promise = api.get("pages");
			expect(panel.isLoading).toStrictEqual(true);
			await promise;
		});

		it("should reset panel.isLoading after request completes", async () => {
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages");
			expect(panel.isLoading).toStrictEqual(false);
		});

		it("should not set panel.isLoading for silent requests", async () => {
			const panel = makePanel();
			const api = new Api(panel);
			const promise = api.get("pages", undefined, undefined, true);
			expect(panel.isLoading).toStrictEqual(false);
			await promise;
		});

		it("should return data.data for model type responses", async () => {
			vi.mocked(mockRequest).mockResolvedValueOnce(
				makeResponse({
					type: "model",
					data: { id: "test", title: "Test Page" }
				})
			);
			const api = new Api(makePanel());
			const result = await api.get<{ id: string; title: string }>("pages/test");
			expect(result).toStrictEqual({ id: "test", title: "Test Page" });
		});

		it("should return raw json for non-model responses", async () => {
			vi.mocked(mockRequest).mockResolvedValueOnce(
				makeResponse({ items: ["a", "b"] })
			);
			const api = new Api(makePanel());
			const result = await api.get("pages");
			expect(result).toStrictEqual({ items: ["a", "b"] });
		});

		it("should track active requests and clear them after completion", async () => {
			const api = new Api(makePanel());
			const promise = api.get("pages");
			expect(api.requests).toHaveLength(1);
			await promise;
			expect(api.requests).toHaveLength(0);
		});

		it("should not override GET even when methodOverride is enabled", async () => {
			const api = new Api(
				makePanel({ config: { api: { methodOverride: true } } })
			);
			await api.get("pages");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({ method: "GET" })
			);
		});

		it("should not override POST even when methodOverride is enabled", async () => {
			const api = new Api(
				makePanel({ config: { api: { methodOverride: true } } })
			);
			await api.post("pages", {});
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({ method: "POST" })
			);
		});

		it("should rewrite PATCH as POST with x-http-method-override when methodOverride is enabled", async () => {
			const api = new Api(
				makePanel({ config: { api: { methodOverride: true } } })
			);
			await api.patch("pages/test", {});
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.objectContaining({
					method: "POST",
					headers: expect.objectContaining({
						"x-http-method-override": "PATCH"
					})
				})
			);
		});

		it("should rewrite DELETE as POST with x-http-method-override when methodOverride is enabled", async () => {
			const api = new Api(
				makePanel({ config: { api: { methodOverride: true } } })
			);
			await api.delete("pages/test");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages/test",
				expect.objectContaining({
					method: "POST",
					headers: expect.objectContaining({
						"x-http-method-override": "DELETE"
					})
				})
			);
		});

		it("should forward custom headers from options", async () => {
			const api = new Api(makePanel());
			await api.request("pages", { method: "GET", headers: { "x-custom": "value" } });
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({
					headers: expect.objectContaining({ "x-custom": "value" })
				})
			);
		});

		it("should always include x-language header", async () => {
			const api = new Api(makePanel({ language: { code: "de" } }));
			await api.get("pages");
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({
					headers: expect.objectContaining({ "x-language": "de" })
				})
			);
		});

		it("should forward globals option", async () => {
			const api = new Api(makePanel());
			await api.request("pages", { method: "GET", globals: ["site", "user"] });
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({ globals: ["site", "user"] })
			);
		});

		it("should forward signal option for abort controllers", async () => {
			const api = new Api(makePanel());
			const controller = new AbortController();
			await api.request("pages", { method: "GET", signal: controller.signal });
			expect(vi.mocked(mockRequest)).toHaveBeenCalledWith(
				"https://example.com/api/pages",
				expect.objectContaining({ signal: controller.signal })
			);
		});
	});
});
