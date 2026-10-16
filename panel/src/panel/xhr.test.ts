import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { isAbortError } from "@/helpers/error";
import { flush, MockXHR } from "@test/unit/xhr";
import { send } from "./xhr";

describe("panel.xhr", () => {
	describe("send()", () => {
		beforeEach(() => {
			MockXHR.instances = [];
			vi.stubGlobal("XMLHttpRequest", MockXHR);
		});

		afterEach(() => vi.unstubAllGlobals());

		it("should send method, url, headers and body", async () => {
			const data = new FormData();
			data.append("file", new File(["content"], "a.txt"));

			const req = new Request("https://example.com/api/files", {
				method: "POST",
				body: data,
				headers: { "x-csrf": "dev" }
			});

			send(req, () => {});
			await flush();

			const xhr = MockXHR.instances[0];
			expect(xhr.method).toBe("POST");
			expect(xhr.url).toBe("https://example.com/api/files");
			expect(xhr.headers["x-csrf"]).toBe("dev");
			expect(xhr.headers["content-type"]).toMatch(/^multipart\/form-data/);
			expect(xhr.body).toBeInstanceOf(Blob);
		});

		it("should resolve with a response", async () => {
			const req = new Request("https://example.com/api", { method: "POST" });
			const promise = send(req, () => {});
			await flush();

			MockXHR.instances[0].emitLoad(
				'{"status":"ok"}',
				200,
				"Content-Type: application/json\r\nX-Test: a: b\r\n"
			);

			const response = await promise;
			expect(response.status).toBe(200);
			expect(response.statusText).toBe("OK");
			expect(response.url).toBe("https://example.com/api");
			expect(response.headers.get("content-type")).toBe("application/json");
			expect(response.headers.get("x-test")).toBe("a: b");
			expect(await response.text()).toBe('{"status":"ok"}');
		});

		it("should resolve after a redirect within the origin", async () => {
			const req = new Request("/api", { method: "POST", mode: "same-origin" });
			const promise = send(req, () => {});
			await flush();

			MockXHR.instances[0].emitLoad("{}", 200, "", location.origin + "/other");

			const response = await promise;
			expect(response.url).toBe(location.origin + "/other");
		});

		it("should not send a same-origin request to another origin", async () => {
			const req = new Request("https://example.com/api", {
				method: "POST",
				mode: "same-origin"
			});

			await expect(send(req, () => {})).rejects.toBeInstanceOf(TypeError);
			expect(MockXHR.instances).toHaveLength(0);
		});

		it("should not resolve a response from another origin", async () => {
			const req = new Request("/api", { method: "POST", mode: "same-origin" });
			const promise = send(req, () => {});
			await flush();

			// e.g. after a redirect to another origin
			MockXHR.instances[0].emitLoad(
				'{"<info>":"<img src=x onerror=alert(1)>"}',
				200,
				"Content-Type: application/json\r\n",
				"https://example.com/steal"
			);

			await expect(promise).rejects.toBeInstanceOf(TypeError);
		});

		it("should resolve without body for a 204 response", async () => {
			const req = new Request("https://example.com/api", { method: "POST" });
			const promise = send(req, () => {});
			await flush();

			MockXHR.instances[0].emitLoad("", 204);

			const response = await promise;
			expect(response.status).toBe(204);
			expect(response.body).toBeNull();
		});

		it("should report the upload progress", async () => {
			const progress = vi.fn();
			const req = new Request("https://example.com/api", { method: "POST" });
			send(req, progress);
			await flush();

			MockXHR.instances[0].emitProgress(1, 3);
			MockXHR.instances[0].emitProgress(1, 3, false);

			expect(progress).toHaveBeenCalledExactlyOnceWith(33);
		});

		it("should reject network errors with a TypeError", async () => {
			const req = new Request("https://example.com/api", { method: "POST" });
			const promise = send(req, () => {});
			await flush();

			MockXHR.instances[0].emitError();

			await expect(promise).rejects.toBeInstanceOf(TypeError);
		});

		it("should abort with the signal's reason", async () => {
			const controller = new AbortController();
			const req = new Request("https://example.com/api", {
				method: "POST",
				signal: controller.signal
			});
			const promise = send(req, () => {});
			await flush();

			controller.abort();

			const error = await promise.catch((reason) => reason);
			expect(isAbortError(error)).toBe(true);
			expect(MockXHR.instances[0].aborted).toBe(true);
		});

		it("should not send when aborted already", async () => {
			const controller = new AbortController();
			controller.abort();

			const req = new Request("https://example.com/api", {
				method: "POST",
				signal: controller.signal
			});

			const error = await send(req, () => {}).catch((reason) => reason);
			expect(isAbortError(error)).toBe(true);
			expect(MockXHR.instances[0].body).toBeNull();
		});
	});
});
