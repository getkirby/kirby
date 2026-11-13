import { afterEach, describe, expect, it, vi } from "vitest";
import webauthn from "./webauthn";

/**
 * Stubs `PublicKeyCredential` with parsers that wrap
 * the server options, so the tests can see what reached
 * `navigator.credentials`
 */
function stubParsers() {
	vi.stubGlobal("PublicKeyCredential", {
		parseCreationOptionsFromJSON: (options: unknown) => ({ parsed: options }),
		parseRequestOptionsFromJSON: (options: unknown) => ({ parsed: options })
	});
}

/**
 * Stubs `navigator.credentials.<method>` with a credential
 * whose `toJSON()` returns the given payload and captures
 * the options it was called with
 */
function stubCredentials(method: "create" | "get", json: unknown) {
	const calls: Array<{ publicKey: unknown }> = [];

	stubParsers();
	vi.stubGlobal("navigator", {
		credentials: {
			[method]: (options: { publicKey: unknown }) => {
				calls.push(options);
				return Promise.resolve({ toJSON: () => json });
			}
		}
	});

	return calls;
}

/**
 * Stubs `navigator.credentials.<method>` with a rejection
 */
function stubRejection(method: "create" | "get", reason: Error) {
	stubParsers();
	vi.stubGlobal("navigator", {
		credentials: { [method]: () => Promise.reject(reason) }
	});
}

const options = { challenge: "AQID" } as never;

describe("webauthn", () => {
	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("create()", () => {
		it("parses the options, runs the ceremony and hands the JSON to onComplete", async () => {
			const json = { id: "cred-1", rawId: "AQID", response: {} };
			const calls = stubCredentials("create", json);
			const onComplete = vi.fn();

			await webauthn.create(options, onComplete);

			expect(calls).toEqual([{ publicKey: { parsed: options } }]);
			expect(onComplete).toHaveBeenCalledWith(json);
		});

		it("maps invalid options to a message and skips the ceremony", async () => {
			const create = vi.fn();

			vi.stubGlobal("PublicKeyCredential", {
				parseCreationOptionsFromJSON: () => {
					throw new TypeError("invalid");
				}
			});
			vi.stubGlobal("navigator", { credentials: { create } });

			const onError = vi.fn();
			await webauthn.create(options, () => {}, onError);

			expect(create).not.toHaveBeenCalled();
			expect(onError).toHaveBeenCalledWith("invalid");
		});

		it("maps a failed ceremony to a message and skips onComplete", async () => {
			stubRejection("create", new Error("boom"));

			const onComplete = vi.fn();
			const onError = vi.fn();

			await webauthn.create(options, onComplete, onError);

			expect(onComplete).not.toHaveBeenCalled();
			expect(onError).toHaveBeenCalledWith("boom");
		});

		it("maps a SecurityError to a translated message", async () => {
			window.panel = {
				t: (key: string) => `t:${key}`
			} as unknown as typeof window.panel;

			const securityError = new Error("raw");
			securityError.name = "SecurityError";
			stubRejection("create", securityError);

			const onError = vi.fn();
			await webauthn.create(options, () => {}, onError);

			expect(onError).toHaveBeenCalledWith("t:error.login.webauthn.security");
		});

		it("stays silent when the user cancels", async () => {
			for (const name of ["NotAllowedError", "AbortError"]) {
				const cancel = new Error("cancelled");
				cancel.name = name;
				stubRejection("create", cancel);

				const onError = vi.fn();
				await webauthn.create(options, () => {}, onError);

				expect(onError).not.toHaveBeenCalled();
			}
		});
	});

	describe("get()", () => {
		it("parses the options, runs the ceremony and hands the JSON to onComplete", async () => {
			const json = { id: "cred-1", rawId: "AQID", response: {} };
			const calls = stubCredentials("get", json);
			const onComplete = vi.fn();

			await webauthn.get(options, onComplete);

			expect(calls).toEqual([{ publicKey: { parsed: options } }]);
			expect(onComplete).toHaveBeenCalledWith(json);
		});

		it("maps invalid options to a message and skips the ceremony", async () => {
			const get = vi.fn();

			vi.stubGlobal("PublicKeyCredential", {
				parseRequestOptionsFromJSON: () => {
					throw new TypeError("invalid");
				}
			});
			vi.stubGlobal("navigator", { credentials: { get } });

			const onError = vi.fn();
			await webauthn.get(options, () => {}, onError);

			expect(get).not.toHaveBeenCalled();
			expect(onError).toHaveBeenCalledWith("invalid");
		});

		it("maps a failed ceremony to a message and skips onComplete", async () => {
			stubRejection("get", new Error("boom"));

			const onComplete = vi.fn();
			const onError = vi.fn();

			await webauthn.get(options, onComplete, onError);

			expect(onComplete).not.toHaveBeenCalled();
			expect(onError).toHaveBeenCalledWith("boom");
		});

		it("stays silent when the user cancels", async () => {
			const cancel = new Error("cancelled");
			cancel.name = "NotAllowedError";
			stubRejection("get", cancel);

			const onError = vi.fn();
			await webauthn.get(options, () => {}, onError);

			expect(onError).not.toHaveBeenCalled();
		});
	});
});
