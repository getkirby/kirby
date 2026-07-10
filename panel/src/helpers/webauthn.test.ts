import { afterEach, describe, expect, it, vi } from "vitest";
import webauthn, {
	type SerializedAssertion,
	type SerializedAttestation
} from "./webauthn";

/**
 * Builds an ArrayBuffer from a list of byte values
 */
const buffer = (...bytes: number[]) => new Uint8Array(bytes).buffer;

/**
 * Stubs `navigator.credentials.<method>` with a resolver
 * that captures the options it was called with
 */
const stubCredentials = (method: "create" | "get", credential: unknown) => {
	const calls: Array<{ publicKey: Record<string, unknown> }> = [];

	vi.stubGlobal("navigator", {
		credentials: {
			[method]: (options: { publicKey: Record<string, unknown> }) => {
				calls.push(options);
				return Promise.resolve(credential);
			}
		}
	});

	return calls;
};

/**
 * Stubs `navigator.credentials.<method>` with a rejection
 */
const stubRejection = (method: "create" | "get", reason: Error) => {
	vi.stubGlobal("navigator", {
		credentials: { [method]: () => Promise.reject(reason) }
	});
};

describe("webauthn", () => {
	describe("create()", () => {
		afterEach(() => vi.unstubAllGlobals());

		it("decodes the options, runs the ceremony and hands the attestation to onComplete", async () => {
			const calls = stubCredentials("create", {
				id: "cred-1",
				type: "public-key",
				rawId: buffer(1, 2, 3),
				response: {
					clientDataJSON: buffer(255),
					attestationObject: buffer(255, 224)
				}
			});

			let attestation: SerializedAttestation | undefined;

			await webauthn.create(
				{ challenge: "AQID" },
				(payload) => (attestation = payload)
			);

			// the server options were decoded before the ceremony
			expect(calls[0].publicKey.challenge).toBeInstanceOf(ArrayBuffer);

			// the attestation was serialized
			expect(attestation).toEqual({
				id: "cred-1",
				rawId: "AQID",
				client: "_w",
				attestation: "_-A"
			});
			expect(attestation).not.toHaveProperty("type");
		});

		it("serializes a missing rawId as null", async () => {
			stubCredentials("create", {
				id: "cred-1",
				rawId: null,
				response: { clientDataJSON: buffer(1), attestationObject: buffer(1) }
			});

			let attestation: SerializedAttestation | undefined;

			await webauthn.create(
				{ challenge: "AQID" },
				(payload) => (attestation = payload)
			);

			expect(attestation!.rawId).toBeNull();
		});

		it("decodes excludeCredentials, dropping invalid entries", async () => {
			const calls = stubCredentials("create", {
				id: "cred-1",
				rawId: buffer(1),
				response: { clientDataJSON: buffer(1), attestationObject: buffer(1) }
			});

			await webauthn.create(
				{
					challenge: "AQID",
					excludeCredentials: [
						{ id: "AQID", transports: ["internal"] },
						{ id: 123 },
						null
					]
				} as Parameters<typeof webauthn.create>[0],
				() => {}
			);

			const excludeCredentials = calls[0].publicKey
				.excludeCredentials as Array<{
				id: ArrayBuffer;
				transports?: string[];
			}>;
			expect(excludeCredentials).toHaveLength(1);
			expect(new Uint8Array(excludeCredentials[0].id)).toEqual(
				new Uint8Array([1, 2, 3])
			);
			expect(excludeCredentials[0].transports).toEqual(["internal"]);
		});

		it("maps a failed ceremony to a message and skips onComplete", async () => {
			stubRejection("create", new Error("boom"));

			const onComplete = vi.fn();

			let message: string | undefined;

			await webauthn.create(
				{ challenge: "AQID" },
				onComplete,
				(m) => (message = m)
			);

			expect(onComplete).not.toHaveBeenCalled();
			expect(message).toBe("boom");
		});

		it("maps a SecurityError to a translated message", async () => {
			window.panel = {
				t: (key: string) => `t:${key}`
			} as unknown as typeof window.panel;

			const securityError = new Error("raw");
			securityError.name = "SecurityError";
			stubRejection("create", securityError);

			let message: string | undefined;

			await webauthn.create(
				{ challenge: "AQID" },
				() => {},
				(m) => (message = m)
			);

			expect(message).toBe("t:error.login.webauthn.security");
		});

		it("stays silent when the user cancels", async () => {
			for (const name of ["NotAllowedError", "AbortError"]) {
				const cancel = new Error("cancelled");
				cancel.name = name;
				stubRejection("create", cancel);

				const onError = vi.fn();
				await webauthn.create({ challenge: "AQID" }, () => {}, onError);

				expect(onError).not.toHaveBeenCalled();
			}
		});
	});

	describe("get()", () => {
		afterEach(() => vi.unstubAllGlobals());

		/**
		 * Minimal, serializable login credential for tests that only care
		 * about how the server options were decoded, not the assertion payload
		 */
		const ASSERTION = {
			id: "x",
			rawId: buffer(1),
			response: {
				clientDataJSON: buffer(1),
				authenticatorData: buffer(1),
				signature: buffer(1),
				userHandle: null
			}
		};

		it("decodes the options, runs the ceremony and hands the assertion to onComplete", async () => {
			const calls = stubCredentials("get", {
				id: "cred-1",
				rawId: buffer(1, 2, 3),
				response: {
					clientDataJSON: buffer(255),
					authenticatorData: buffer(0),
					signature: buffer(1, 2, 3),
					userHandle: buffer(255)
				}
			});

			let assertion: SerializedAssertion | undefined;

			await webauthn.get(
				{ challenge: "AQID" },
				(payload) => (assertion = payload)
			);

			// the server options were decoded before the ceremony
			expect(calls[0].publicKey.challenge).toBeInstanceOf(ArrayBuffer);

			// the assertion was serialized for the backend
			expect(assertion).toEqual({
				id: "cred-1",
				rawId: "AQID",
				client: "_w",
				authenticator: "AA",
				signature: "AQID",
				user: "_w"
			});
		});

		it("serializes a null user when there is no user handle", async () => {
			stubCredentials("get", {
				id: "cred-1",
				rawId: buffer(1),
				response: {
					clientDataJSON: buffer(1),
					authenticatorData: buffer(1),
					signature: buffer(1),
					userHandle: null
				}
			});

			let assertion: SerializedAssertion | undefined;

			await webauthn.get(
				{ challenge: "AQID" },
				(payload) => (assertion = payload)
			);

			expect(assertion!.user).toBeNull();
		});

		it("decodes an RFC 2047 wrapped, url-safe challenge", async () => {
			const calls = stubCredentials("get", ASSERTION);

			await webauthn.get({ challenge: "=?BINARY?B?_-A?=" }, () => {});

			expect(
				new Uint8Array(calls[0].publicKey.challenge as ArrayBuffer)
			).toEqual(new Uint8Array([255, 224]));
		});

		it("passes an already-decoded challenge through untouched", async () => {
			const challenge = buffer(9, 9, 9);
			const calls = stubCredentials("get", ASSERTION);

			await webauthn.get({ challenge }, () => {});

			expect(calls[0].publicKey.challenge).toBe(challenge);
		});

		it("decodes the user id and preserves other user fields", async () => {
			const calls = stubCredentials("get", ASSERTION);

			await webauthn.get({ user: { id: "AQID", name: "ada" } }, () => {});

			const user = calls[0].publicKey.user as { id: ArrayBuffer; name: string };
			expect(new Uint8Array(user.id)).toEqual(new Uint8Array([1, 2, 3]));
			expect(user.name).toBe("ada");
		});

		it("decodes allowCredentials, drops invalid entries and keeps extra props", async () => {
			const calls = stubCredentials("get", ASSERTION);

			await webauthn.get(
				{
					allowCredentials: [
						{ id: "AQID", transports: ["internal"] },
						{ id: 123 },
						null
					]
				} as Parameters<typeof webauthn.get>[0],
				() => {}
			);

			const allowCredentials = calls[0].publicKey.allowCredentials as Array<{
				id: ArrayBuffer;
				transports?: string[];
			}>;
			expect(allowCredentials).toHaveLength(1);
			expect(new Uint8Array(allowCredentials[0].id)).toEqual(
				new Uint8Array([1, 2, 3])
			);
			expect(allowCredentials[0].transports).toEqual(["internal"]);
		});

		it("does not mutate the caller's options", async () => {
			stubCredentials("get", ASSERTION);

			const input = {
				challenge: "AQID",
				user: { id: "AQID", name: "ada" },
				allowCredentials: [{ id: "AQID" }]
			};
			await webauthn.get(input, () => {});

			expect(input.challenge).toBe("AQID");
			expect(input.user.id).toBe("AQID");
			expect(input.allowCredentials[0].id).toBe("AQID");
		});

		it("maps a failed ceremony to a message and skips onComplete", async () => {
			stubRejection("get", new Error("boom"));

			const onComplete = vi.fn();

			let message: string | undefined;

			await webauthn.get(
				{ challenge: "AQID" },
				onComplete,
				(m) => (message = m)
			);

			expect(onComplete).not.toHaveBeenCalled();
			expect(message).toBe("boom");
		});

		it("stays silent when the user cancels", async () => {
			const cancel = new Error("cancelled");
			cancel.name = "NotAllowedError";
			stubRejection("get", cancel);

			const onError = vi.fn();
			await webauthn.get({ challenge: "AQID" }, () => {}, onError);

			expect(onError).not.toHaveBeenCalled();
		});
	});
});
