import { afterEach, describe, expect, it, vi } from "vitest";
import AuthError from "@/errors/AuthError";
import ExternalRequestError from "@/errors/ExternalRequestError";
import JsonRequestError from "@/errors/JsonRequestError";
import OfflineError from "@/errors/OfflineError";
import RedirectError from "@/errors/RedirectError";
import RequestError from "@/errors/RequestError";
import { HtmlString } from "./html";
import {
	body,
	external,
	globals,
	headers,
	redirect,
	request,
	responder,
	safeFetch
} from "./request";

function jsonResponse(body: unknown, status = 200) {
	return new Response(JSON.stringify(body), {
		status,
		headers: { "Content-Type": "application/json" }
	});
}

function stubFetch(response: Response) {
	const mock = vi.fn().mockResolvedValue(response);
	vi.stubGlobal("fetch", mock);
	return mock;
}

describe("panel.request", () => {
	describe("body()", () => {
		it("should create body from object", () => {
			expect(body({ a: "A" })).toStrictEqual('{"a":"A"}');
		});

		it("should create body from string", () => {
			expect(body("test")).toStrictEqual("test");
		});

		it("should create body from FormData", () => {
			const formData = new FormData();
			formData.append("a", "A");
			expect(body(formData)).toStrictEqual('{"a":"A"}');
		});

		it("should create body from HTMLFormElement", () => {
			const form = document.createElement("form");
			const input = document.createElement("input");
			input.name = "a";
			input.value = "A";
			form.appendChild(input);
			expect(body(form)).toStrictEqual('{"a":"A"}');
		});

		it("should return null for null", () => {
			expect(body(null)).toStrictEqual(null);
		});

		it("should return undefined for undefined", () => {
			expect(body(undefined)).toStrictEqual(undefined);
		});
	});

	describe("external()", () => {
		afterEach(() => {
			vi.restoreAllMocks();
			vi.unstubAllGlobals();
		});

		it("should not send any Panel options", async () => {
			const mock = stubFetch(jsonResponse({}));

			await external("https://example.com/api", {
				csrf: "secret",
				globals: "$language",
				language: "de",
				referrer: "/pages/test"
			});

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("x-csrf")).toStrictEqual(null);
			expect(req.headers.get("x-language")).toStrictEqual(null);
			expect(req.headers.get("x-panel")).toStrictEqual(null);
			expect(req.headers.get("x-panel-globals")).toStrictEqual(null);
			expect(req.referrer).not.toContain("/pages/test");
		});

		it("should not send a content type without a body", async () => {
			const mock = stubFetch(jsonResponse({}));

			await external("https://example.com/api");

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("content-type")).toStrictEqual(null);
		});

		it("should send a JSON body with content type", async () => {
			const mock = stubFetch(jsonResponse({}));

			await external("https://example.com/api", {
				method: "POST",
				body: { a: "A" }
			});

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("content-type")).toStrictEqual("application/json");
			expect(await req.text()).toStrictEqual('{"a":"A"}');
		});

		it("should prefer a custom content type", async () => {
			const mock = stubFetch(jsonResponse({}));

			await external("https://example.com/api", {
				method: "POST",
				body: "Hello",
				headers: { "Content-Type": "text/plain" }
			});

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("content-type")).toStrictEqual("text/plain");
		});

		it("should add query parameters", async () => {
			const mock = stubFetch(jsonResponse({}));

			await external("https://example.com/api", { query: { search: "Foo" } });

			const req = mock.mock.calls[0][0] as Request;
			expect(req.url).toStrictEqual("https://example.com/api?search=Foo");
		});

		it("should parse a JSON response", async () => {
			stubFetch(jsonResponse({ message: "ok" }));

			const { response } = await external("https://example.com/api");
			expect(response.json).toStrictEqual({ message: "ok" });
			expect(response.text).toStrictEqual('{"message":"ok"}');
		});

		it("should not rewrap <key> payloads into HtmlString instances", async () => {
			stubFetch(jsonResponse({ "<help>": "<b>untrusted</b>" }));

			const { response } = await external("https://example.com/api");
			expect(response.json).toStrictEqual({ "<help>": "<b>untrusted</b>" });
		});

		it("should keep json null for a text response", async () => {
			stubFetch(
				new Response("Hello", { headers: { "Content-Type": "text/plain" } })
			);

			const { response } = await external("https://example.com/api");
			expect(response.json).toStrictEqual(null);
			expect(response.text).toStrictEqual("Hello");
		});

		it("should keep invalid JSON as text", async () => {
			stubFetch(
				new Response("<script>alert(1)</script>", {
					headers: { "Content-Type": "application/json" }
				})
			);

			const { response } = await external("https://example.com/api");
			expect(response.json).toStrictEqual(null);
			expect(response.text).toStrictEqual("<script>alert(1)</script>");
		});

		it("should throw ExternalRequestError for a non-ok response", async () => {
			stubFetch(jsonResponse({ message: "Server Error" }, 500));
			const t = vi.spyOn(window.panel, "t");

			const error = await external("https://example.com/api").catch((e) => e);
			expect(error).toBeInstanceOf(ExternalRequestError);
			expect(error).not.toBeInstanceOf(RequestError);
			expect(error.message).toStrictEqual("error.request.failed");
			expect(error.response.json).toStrictEqual({ message: "Server Error" });
			expect(t).toHaveBeenCalledWith("error.request.failed", {
				url: "https://example.com/api"
			});
		});

		it("should not let the response pick the error dialog", async () => {
			stubFetch(
				jsonResponse(
					{
						message: "Please log in again",
						key: "error.form.incomplete",
						exception: "Kirby\\Exception\\FormValidationException",
						details: { password: { label: "Password", message: ["Required"] } },
						editor: "https://example.org",
						trace: [{ url: "javascript:alert(1)" }]
					},
					500
				)
			);

			const error = await external("https://example.com/api").catch((e) => e);
			expect(error.message).toStrictEqual("error.request.failed");
			expect(error.key).toStrictEqual(undefined);
			expect(error.details).toStrictEqual(undefined);
			expect(error.dialog).toStrictEqual(undefined);
		});

		it("should throw ExternalRequestError for a non-ok text response", async () => {
			stubFetch(new Response("Nope", { status: 500 }));

			await expect(external("https://example.com/api")).rejects.toThrow(
				"error.request.failed"
			);
		});

		it("should not go offline on network failure", async () => {
			const error = new TypeError("Failed to fetch");
			vi.stubGlobal("fetch", vi.fn().mockRejectedValue(error));

			await expect(external("https://example.com/api")).rejects.toBe(error);
		});
	});

	describe("globals()", () => {
		it("should create globals from string", () => {
			expect(globals("$language")).toStrictEqual("$language");
		});

		it("should create globals from array", () => {
			expect(globals(["$language", "$user"])).toStrictEqual("$language,$user");
		});

		it("should return undefined for empty array", () => {
			expect(globals([])).toStrictEqual(undefined);
		});

		it("should return undefined without input", () => {
			expect(globals()).toStrictEqual(undefined);
		});
	});

	describe("headers()", () => {
		it("should create default headers", () => {
			expect(headers()).toStrictEqual({
				"content-type": "application/json",
				"x-panel": "true"
			});
		});

		it("should lowercase custom header keys", () => {
			const result = headers({ "X-Foo": "test" });
			expect(result).toHaveProperty("x-foo", "test");
			expect(result).not.toHaveProperty("X-Foo");
		});

		it("should add custom headers", () => {
			expect(headers({ "x-foo": "test" })).toStrictEqual({
				"content-type": "application/json",
				"x-panel": "true",
				"x-foo": "test"
			});
		});

		it("should set options", () => {
			expect(
				headers(
					{},
					{
						csrf: "dev",
						globals: ["$language"],
						language: "de",
						referrer: "/test"
					}
				)
			).toStrictEqual({
				"content-type": "application/json",
				"x-csrf": "dev",
				"x-language": "de",
				"x-panel": "true",
				"x-panel-globals": "$language",
				"x-panel-referrer": "/test"
			});
		});

		it("should not set the language header without a language", () => {
			expect(headers({}, { language: null })).not.toHaveProperty("x-language");
		});
	});

	describe("redirect()", () => {
		it("should throw RedirectError with the target url", () => {
			let caught: unknown;
			expect(() => {
				try {
					redirect("/test");
				} catch (e) {
					caught = e;
					throw e;
				}
			}).toThrow(RedirectError);
			expect((caught as RedirectError).url).toContain("/test");
		});
	});

	describe("request()", () => {
		afterEach(() => vi.unstubAllGlobals());

		it("should redirect cross-origin requests", async () => {
			await expect(request("https://example.com/api")).rejects.toThrow(
				RedirectError
			);
		});

		it("should fetch cross-origin requests when external", async () => {
			const mock = stubFetch(jsonResponse({ message: "ok" }));

			const { response } = await request("https://example.com/api", {
				csrf: "secret",
				external: true
			});

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("x-csrf")).toStrictEqual(null);
			expect(response.json).toStrictEqual({ message: "ok" });
		});

		it("should send Panel headers for same-origin requests", async () => {
			const mock = stubFetch(jsonResponse({ message: "ok" }));

			await request("/api/test", { csrf: "secret" });

			const req = mock.mock.calls[0][0] as Request;
			expect(req.headers.get("x-panel")).toStrictEqual("true");
			expect(req.headers.get("x-csrf")).toStrictEqual("secret");
		});
	});

	describe("responder()", () => {
		const req = new Request("https://example.com/api");

		it("should return parsed response for successful JSON", async () => {
			const { response } = await responder(
				req,
				jsonResponse({ message: "ok" })
			);
			expect(response.status).toBe(200);
			expect(response.json).toStrictEqual({ message: "ok" });
			expect(response.text).toStrictEqual(JSON.stringify({ message: "ok" }));
		});

		it("should rewrap <key> payloads into HtmlString instances", async () => {
			const { response } = await responder(
				req,
				jsonResponse({
					dialog: {
						props: {
							"<help>": "<b>trusted</b>",
							text: "plain"
						}
					}
				})
			);

			const props = (response.json.dialog as Record<string, unknown>).props as {
				help: HtmlString;
				text: string;
			};

			expect(props.help).toBeInstanceOf(HtmlString);
			expect(props.help.toString()).toBe("<b>trusted</b>");
			expect(props.text).toBe("plain");
		});

		it("should throw JsonRequestError for invalid JSON", async () => {
			const raw = new Response("not-json", {
				status: 200,
				headers: { "Content-Type": "application/json" }
			});
			await expect(responder(req, raw)).rejects.toThrow(JsonRequestError);
		});

		it("should rethrow AbortError without wrapping in JsonRequestError", async () => {
			const abortError = Object.assign(new Error("Aborted"), {
				name: "AbortError"
			});
			const raw = new Response(null, {
				status: 200,
				headers: { "Content-Type": "application/json" }
			});
			vi.spyOn(raw, "text").mockRejectedValue(abortError);
			await expect(responder(req, raw)).rejects.toThrow("Aborted");
			await expect(responder(req, raw)).rejects.not.toThrow(JsonRequestError);
		});

		it("should redirect on non-JSON content type", async () => {
			const raw = new Response("<html>", {
				status: 200,
				headers: { "Content-Type": "text/html" }
			});
			await expect(responder(req, raw)).rejects.toThrow(RedirectError);
		});

		it("should throw AuthError for 401", async () => {
			await expect(
				responder(req, jsonResponse({ message: "Unauthenticated" }, 401))
			).rejects.toThrow(AuthError);
		});

		it("should throw RequestError for non-ok response", async () => {
			await expect(
				responder(req, jsonResponse({ message: "Server Error" }, 500))
			).rejects.toThrow(RequestError);
		});
	});

	describe("safeFetch()", () => {
		afterEach(() => vi.unstubAllGlobals());

		it("should throw OfflineError on network failure", async () => {
			vi.stubGlobal(
				"fetch",
				vi.fn().mockRejectedValue(new Error("Network error"))
			);
			const req = new Request("https://example.com/api");
			await expect(safeFetch(req)).rejects.toThrow(OfflineError);
		});

		it("should rethrow AbortError without wrapping", async () => {
			const abortError = Object.assign(new Error("Aborted"), {
				name: "AbortError"
			});
			vi.stubGlobal("fetch", vi.fn().mockRejectedValue(abortError));
			const req = new Request("https://example.com/api");
			await expect(safeFetch(req)).rejects.toThrow("Aborted");
			await expect(safeFetch(req)).rejects.not.toThrow(OfflineError);
		});
	});
});
