import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import Api from "./index";
import type Panel from "@/panel/panel";

function makePanel(overrides: Record<string, unknown> = {}) {
	return {
		system: { csrf: "test-csrf" },
		urls: { api: "http://localhost:3000/api/" },
		config: {},
		language: { code: "en" },
		isOffline: false,
		isLoading: false,
		...overrides
	} as unknown as Panel;
}

function mockFetch(json: Record<string, unknown> = { result: "ok" }) {
	vi.mocked(fetch).mockResolvedValueOnce(
		new Response(JSON.stringify(json), {
			headers: { "Content-Type": "application/json" }
		})
	);
}

function lastRequest(): Request {
	const calls = vi.mocked(fetch).mock.calls;
	return calls[calls.length - 1][0] as Request;
}

describe("api", () => {
	beforeEach(() => {
		vi.useFakeTimers();
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
			const panel = makePanel({
				urls: { api: "https://example.com/api/" }
			});
			const api = new Api(panel);
			expect(api.endpoint).toStrictEqual("https://example.com/api");
		});

		it("should default methodOverride to false when not configured", () => {
			const api = new Api(makePanel({ config: {} }));
			expect(api.methodOverride).toStrictEqual(false);
		});
	});

	describe("delete()", () => {
		it("should delegate to post with DELETE method", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.delete("pages/test");
			const req = lastRequest();
			expect(req.url).toStrictEqual("http://localhost:3000/api/pages/test");
			expect(req.method).toStrictEqual("DELETE");
		});
	});

	describe("get()", () => {
		it("should call request with GET method", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages");
			const req = lastRequest();
			expect(req.url).toStrictEqual("http://localhost:3000/api/pages");
			expect(req.method).toStrictEqual("GET");
		});

		it("should append query params to path", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages", { search: "test" });
			expect(lastRequest().url).toContain("search=test");
		});

		it("should not append a query string for empty query object", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages", {});
			expect(lastRequest().url).not.toContain("?");
		});
	});

	describe("patch()", () => {
		it("should delegate to post with PATCH method", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.patch("pages/test", { title: "Updated" });
			const req = lastRequest();
			expect(req.url).toStrictEqual("http://localhost:3000/api/pages/test");
			expect(req.method).toStrictEqual("PATCH");
			expect(await req.text()).toStrictEqual(
				JSON.stringify({ title: "Updated" })
			);
		});
	});

	describe("ping()", () => {
		it("should call auth.ping after 5 minutes when online", () => {
			const panel = makePanel({ isOffline: false });
			const api = new Api(panel);
			const spy = vi.spyOn(api.auth, "ping").mockResolvedValue(undefined);

			vi.advanceTimersByTime(5 * 60 * 1000);

			expect(spy).toHaveBeenCalledOnce();
		});

		it("should not call auth.ping when offline", () => {
			const panel = makePanel({ isOffline: true });
			const api = new Api(panel);
			const spy = vi.spyOn(api.auth, "ping").mockResolvedValue(undefined);

			vi.advanceTimersByTime(5 * 60 * 1000);

			expect(spy).not.toHaveBeenCalled();
		});

		it("should replace the previous interval when called again", () => {
			const panel = makePanel({ isOffline: false });
			const api = new Api(panel);
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
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.post("pages", { title: "Test" });
			const req = lastRequest();
			expect(req.url).toStrictEqual("http://localhost:3000/api/pages");
			expect(req.method).toStrictEqual("POST");
			expect(await req.text()).toStrictEqual(JSON.stringify({ title: "Test" }));
		});
	});

	describe("request()", () => {
		it("should construct URL from endpoint and path", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages/test");
			expect(lastRequest().url).toStrictEqual(
				"http://localhost:3000/api/pages/test"
			);
		});

		it("should strip leading slash from path", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("/pages/test");
			expect(lastRequest().url).toStrictEqual(
				"http://localhost:3000/api/pages/test"
			);
		});

		it("should set panel.isLoading to true during a request", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			const promise = api.get("pages");
			expect(panel.isLoading).toStrictEqual(true);
			await promise;
		});

		it("should reset panel.isLoading after request completes", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.get("pages");
			expect(panel.isLoading).toStrictEqual(false);
		});

		it("should not set panel.isLoading for silent requests", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			const promise = api.get("pages", undefined, undefined, true);
			expect(panel.isLoading).toStrictEqual(false);
			await promise;
		});

		it("should return data.data for model type responses", async () => {
			mockFetch({
				type: "model",
				data: { id: "test", title: "Test Page" }
			});
			const panel = makePanel();
			const api = new Api(panel);
			const result = await api.get("pages/test");
			expect(result).toStrictEqual({ id: "test", title: "Test Page" });
		});

		it("should return raw json for non-model responses", async () => {
			mockFetch({ items: ["a", "b"] });
			const panel = makePanel();
			const api = new Api(panel);
			const result = await api.get("pages");
			expect(result).toStrictEqual({ items: ["a", "b"] });
		});

		it("should track active requests and clear them after completion", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			const promise = api.get("pages");
			expect(api.requests).toHaveLength(1);
			await promise;
			expect(api.requests).toHaveLength(0);
		});

		it("should not override GET even when methodOverride is enabled", async () => {
			mockFetch();
			const panel = makePanel({
				config: { api: { methodOverride: true } }
			});
			const api = new Api(panel);
			await api.get("pages");
			expect(lastRequest().method).toStrictEqual("GET");
		});

		it("should not override POST even when methodOverride is enabled", async () => {
			mockFetch();
			const panel = makePanel({
				config: { api: { methodOverride: true } }
			});
			const api = new Api(panel);
			await api.post("pages", {});
			expect(lastRequest().method).toStrictEqual("POST");
		});

		it("should rewrite PATCH as POST with x-http-method-override when methodOverride is enabled", async () => {
			mockFetch();
			const panel = makePanel({
				config: { api: { methodOverride: true } }
			});
			const api = new Api(panel);
			await api.patch("pages/test", {});
			const req = lastRequest();
			expect(req.method).toStrictEqual("POST");
			expect(req.headers.get("x-http-method-override")).toStrictEqual("PATCH");
		});

		it("should rewrite DELETE as POST with x-http-method-override when methodOverride is enabled", async () => {
			mockFetch();
			const panel = makePanel({
				config: { api: { methodOverride: true } }
			});
			const api = new Api(panel);
			await api.delete("pages/test");
			const req = lastRequest();
			expect(req.method).toStrictEqual("POST");
			expect(req.headers.get("x-http-method-override")).toStrictEqual("DELETE");
		});

		it("should forward custom headers from options", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.request("pages", {
				method: "GET",
				headers: { "x-custom": "value" }
			});
			expect(lastRequest().headers.get("x-custom")).toStrictEqual("value");
		});

		it("should always include x-language header", async () => {
			mockFetch();
			const api = new Api(makePanel({ language: { code: "de" } }));
			await api.get("pages");
			expect(lastRequest().headers.get("x-language")).toStrictEqual("de");
		});

		it("should forward globals option", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			await api.request("pages", { method: "GET", globals: ["site", "user"] });
			expect(lastRequest().headers.get("x-panel-globals")).toStrictEqual(
				"site,user"
			);
		});

		it("should forward signal option for abort controllers", async () => {
			mockFetch();
			const panel = makePanel();
			const api = new Api(panel);
			const controller = new AbortController();
			await api.request("pages", { method: "GET", signal: controller.signal });
			const req = lastRequest();
			expect(req.signal.aborted).toStrictEqual(false);
			controller.abort();
			expect(req.signal.aborted).toStrictEqual(true);
		});
	});
});
