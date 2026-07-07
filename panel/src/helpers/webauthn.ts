/**
 * WebAuthn helper utilities
 * shared between registration and login flows.
 *
 * @example
 * // register a new passkey
 * webauthn.create(publicKey, (attestation) => save(attestation), notify);
 *
 * // verify an existing passkey
 * webauthn.get(publicKey, (assertion) => authorize(assertion), notify);
 *
 * // guard on browser support
 * if (webauthn.isSupported() === false)
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

// Passkey credentials narrowed to the response the DOM only
// types as the base `AuthenticatorResponse`: an attestation
// after registration, an assertion after login
interface AttestationCredential extends PublicKeyCredential {
	response: AuthenticatorAttestationResponse;
}

interface AssertionCredential extends PublicKeyCredential {
	response: AuthenticatorAssertionResponse;
}

// Browser-ready options accepted by both
// navigator.credentials.create() and navigator.credentials.get()
type CredentialOptions = CredentialCreationOptions & CredentialRequestOptions;

// Server-side credential options
// where binary fields are base64url-encoded strings
interface ServerCredential {
	id: string | ArrayBuffer;
	[key: string]: unknown;
}

interface ServerOptions {
	challenge?: string | BufferSource;
	user?: { id: string | BufferSource; [key: string]: unknown };
	excludeCredentials?: Array<ServerCredential | null | undefined>;
	allowCredentials?: Array<ServerCredential | null | undefined>;
	[key: string]: unknown;
}

// Serialized credential payloads for the backend,
// with binary fields encoded as base64url strings
export interface SerializedAttestation {
	id: string;
	rawId: string | null;
	client: string | null;
	attestation: string | null;
}

export interface SerializedAssertion {
	id: string;
	rawId: string | null;
	client: string | null;
	authenticator: string | null;
	signature: string | null;
	user: string | null;
}

/**
 * Encodes an ArrayBuffer as an unpadded base64url string
 *
 * @example
 * base64(new Uint8Array([1, 2, 3]).buffer); // "AQID"
 */
function base64(buffer: ArrayBuffer | null | undefined): string | null {
	if (buffer === null || buffer === undefined) {
		return null;
	}

	let binary = "";

	for (const byte of new Uint8Array(buffer)) {
		binary += String.fromCharCode(byte);
	}

	return btoa(binary)
		.replace(/\+/g, "-")
		.replace(/\//g, "_")
		.replace(/=+$/, "");
}

/**
 * Decodes a base64url string (optionally RFC 2047 wrapped) into an
 * ArrayBuffer; an existing buffer is passed through untouched
 *
 * @example
 * buffer("AQID"); // ArrayBuffer([1, 2, 3])
 */
function buffer(str: string | BufferSource): ArrayBuffer {
	if (typeof str !== "string") {
		return str as ArrayBuffer;
	}

	const RFC_PREFIX = "=?BINARY?B?";
	const RFC_SUFFIX = "?=";

	if (
		str.startsWith(RFC_PREFIX) === true &&
		str.endsWith(RFC_SUFFIX) === true
	) {
		str = str.slice(RFC_PREFIX.length, -RFC_SUFFIX.length);
	}

	const normalized =
		str.replace(/-/g, "+").replace(/_/g, "/") +
		"=".repeat((4 - (str.length % 4)) % 4);

	return Uint8Array.from(atob(normalized), (char) => char.charCodeAt(0)).buffer;
}

/**
 * Creates a new passkey: decodes the server options, prompts the
 * authenticator through `navigator.credentials.create()`, serializes
 * the resulting attestation and hands it to `onComplete`. Any failure
 * is mapped and forwarded to `onError`.
 *
 * @example
 * await create(publicKey, (attestation) => save(attestation), notify);
 */
async function create(
	publicKey: ServerOptions,
	onComplete: (attestation: SerializedAttestation) => unknown,
	onError?: (message: string) => void
): Promise<void> {
	try {
		const options = decode(publicKey);
		const credential = await navigator.credentials.create(options);
		const attestation = serializeAttestation(
			credential as AttestationCredential
		);
		await onComplete(attestation);
	} catch (e) {
		if (onError) {
			const message = error(e as Error);

			if (message !== null) {
				onError(message);
			}
		}
	}
}

/**
 * Decodes the server's base64url credential options into the
 * `{ publicKey }` shape expected by `navigator.credentials`.
 *
 * @example
 * const options = decode(publicKeyFromServer);
 * await navigator.credentials.get(options);
 */
function decode(options: ServerOptions): CredentialOptions {
	const decoded: ServerOptions = { ...options };

	if (typeof decoded.challenge === "string") {
		decoded.challenge = buffer(decoded.challenge);
	}

	if (decoded.user && typeof decoded.user.id === "string") {
		decoded.user = { ...decoded.user, id: buffer(decoded.user.id) };
	}

	for (const key of ["excludeCredentials", "allowCredentials"] as const) {
		const credentials = decoded[key];

		if (Array.isArray(credentials) === true) {
			decoded[key] = credentials
				.filter(
					(item): item is ServerCredential & { id: string } =>
						typeof item?.id === "string"
				)
				.map((item) => ({ ...item, id: buffer(item.id) }));
		}
	}

	// the single cast that bridges loose server JSON to the strict
	// WebAuthn DOM types, so callers stay cast-free
	return { publicKey: decoded } as unknown as CredentialOptions;
}

/**
 * Maps a WebAuthn `DOMException` to a translated message.
 * Returns `null` when the ceremony was cancelled, timed out
 * or found no credential.
 */
function error(error: Error): string | null {
	if (error.name === "NotAllowedError" || error.name === "AbortError") {
		return null;
	}

	if (error.name === "SecurityError") {
		return window.panel.t("error.login.webauthn.security");
	}

	return error.message;
}

/**
 * Verifies an existing passkey: decodes the server options, prompts the
 * authenticator through `navigator.credentials.get()`, serializes the
 * resulting assertion and hands it to `onComplete`. Any failure is
 * mapped and forwarded to `onError`.
 *
 * @example
 * await get(publicKey, (assertion) => authorize(assertion), notify);
 */
async function get(
	publicKey: ServerOptions,
	onComplete: (assertion: SerializedAssertion) => unknown,
	onError?: (message: string) => void
): Promise<void> {
	try {
		const options = decode(publicKey);
		const credential = await navigator.credentials.get(options);
		const assertion = serializeAssertion(credential as AssertionCredential);
		await onComplete(assertion);
	} catch (e) {
		if (onError) {
			const message = error(e as Error);

			if (message !== null) {
				onError(message);
			}
		}
	}
}

/**
 * Checks whether the browser supports passkeys (WebAuthn)
 */
/* v8 ignore next */
function isSupported(): boolean {
	return (
		typeof window?.PublicKeyCredential === "function" &&
		"credentials" in navigator &&
		typeof navigator.credentials.get === "function"
	);
}

/**
 * Serializes a login credential from `navigator.credentials.get()`
 * into a base64url payload for the backend
 *
 * @example
 * const credential = await navigator.credentials.get(options);
 * serializeAssertion(credential); // { id, rawId, client, … }
 */
function serializeAssertion(
	credential: AssertionCredential
): SerializedAssertion {
	const { id, rawId, response } = credential;

	return {
		id,
		rawId: base64(rawId),
		client: base64(response.clientDataJSON),
		authenticator: base64(response.authenticatorData),
		signature: base64(response.signature),
		user: response.userHandle ? base64(response.userHandle) : null
	};
}

/**
 * Serializes a registration credential from
 * `navigator.credentials.create()` into a base64url payload
 * for the backend
 *
 * @example
 * const credential = await navigator.credentials.create(options);
 * serializeAttestation(credential); // { id, rawId, client, attestation }
 */
function serializeAttestation(
	credential: AttestationCredential
): SerializedAttestation {
	const { id, rawId, response } = credential;

	return {
		id,
		rawId: base64(rawId),
		client: base64(response.clientDataJSON),
		attestation: base64(response.attestationObject)
	};
}

export default { create, get, isSupported };
