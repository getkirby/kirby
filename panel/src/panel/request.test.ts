import { afterEach, describe, expect, it, vi } from "vitest";
import AuthError from "@/errors/AuthError";
import JsonRequestError from "@/errors/JsonRequestError";
import OfflineError from "@/errors/OfflineError";
import RedirectError from "@/errors/RedirectError";
import RequestError from "@/errors/RequestError";
import {
	body,
	globals,
	headers,
	redirect,
	responder,
	safeFetch
} from "./request";

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
				"x-fiber": "true"
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
				"x-fiber": "true",
				"x-foo": "test"
			});
		});

		it("should set options", () => {
			expect(
				headers({}, { csrf: "dev", globals: ["$language"], referrer: "/test" })
			).toStrictEqual({
				"content-type": "application/json",
				"x-csrf": "dev",
				"x-fiber": "true",
				"x-fiber-globals": "$language",
				"x-fiber-referrer": "/test"
			});
		});
	});

	describe("redirect()", () => {
		it("should throw RedirectError with the target url", () => {
			try {
				redirect("/test");
			} catch (error) {
				expect(error).toBeInstanceOf(RedirectError);
				expect((error as RedirectError).url).toContain("/test");
			}
		});
	});

	describe("responder()", () => {
		const req = new Request("https://example.com/api");

		function jsonResponse(body: unknown, status = 200) {
			return new Response(JSON.stringify(body), {
				status,
				headers: { "Content-Type": "application/json" }
			});
		}

		it("should return parsed response for successful JSON", async () => {
			const { response } = await responder(
				req,
				jsonResponse({ message: "ok" })
			);
			expect(response.status).toBe(200);
			expect(response.json).toStrictEqual({ message: "ok" });
			expect(response.text).toStrictEqual(JSON.stringify({ message: "ok" }));
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
