/**
 * WebAuthn helper utilities shared between registration and login flows.
 *
 * Handles the string/buffer conversion quirks of the library and
 * keeps browser-facing options in the shape expected by the WebAuthn APIs.
 */
import { clone } from "@/helpers/object.js";

const RFC_PREFIX = "=?BINARY?B?";
const RFC_SUFFIX = "?=";

/**
 * Convert a base64/base64url (or RFC 1342) string to an ArrayBuffer.
 */
function base64UrlToBuffer(base64) {
	if (typeof base64 !== "string") {
		return base64;
	}

	let normalized = base64;

	if (normalized.startsWith(RFC_PREFIX) && normalized.endsWith(RFC_SUFFIX)) {
		normalized = normalized.slice(RFC_PREFIX.length, -RFC_SUFFIX.length);
	}

	normalized = normalized.replace(/-/g, "+").replace(/_/g, "/");

	const padding = normalized.length % 4;

	if (padding) {
		normalized += "=".repeat(4 - padding);
	}

	const binary = atob(normalized);
	const bytes = new Uint8Array(binary.length);

	for (let i = 0; i < binary.length; i++) {
		bytes[i] = binary.charCodeAt(i);
	}

	return bytes.buffer;
}

/**
 * Convert an ArrayBuffer into a base64url string
 */
function bufferToBase64Url(buffer) {
	if (buffer === null || buffer === undefined) {
		return null;
	}

	const bytes = new Uint8Array(buffer);
	let binary = "";

	for (const byte of bytes) {
		binary += String.fromCharCode(byte);
	}

	return btoa(binary)
		.replace(/\+/g, "-")
		.replace(/\//g, "_")
		.replace(/=+$/, "");
}

/**
 * Convert specific keys on the target object
 * from base64/base64url to ArrayBuffer
 */
function toArrayBuffer(target, keys) {
	if (!target) {
		return;
	}

	for (const key of keys) {
		if (target[key]) {
			target[key] = base64UrlToBuffer(target[key]);
		}
	}
}

/**
 * Normalize and decode the WebAuthn publicKey options so they can be
 * passed directly to navigator.credentials.create/get.
 */
export function normalizePublicKeyOptions(input) {
	const options = clone(input ?? {});
	const publicKey = options.publicKey ?? options;

	// unpack challenges and ids
	if (publicKey.challenge) {
		publicKey.challenge = base64UrlToBuffer(publicKey.challenge);
	}

	if (publicKey.user?.id) {
		publicKey.user.id = base64UrlToBuffer(publicKey.user.id);
	}

	// navigator.credentials.create excludes
	if (Array.isArray(publicKey.excludeCredentials)) {
		publicKey.excludeCredentials = publicKey.excludeCredentials
			.filter((item) => item && typeof item === "object")
			.map((item) => {
				const normalized = { ...item };
				toArrayBuffer(normalized, ["id"]);
				return normalized;
			});
	}

	// navigator.credentials.get allows
	if (Array.isArray(publicKey.allowCredentials)) {
		publicKey.allowCredentials = publicKey.allowCredentials
			.filter((item) => item && typeof item === "object")
			.map((item) => {
				const normalized = { ...item };
				toArrayBuffer(normalized, ["id"]);
				return normalized;
			});
	}

	return { publicKey };
}

/**
 * Serialize a credential returned from navigator.credentials.create
 * into a payload suitable for the backend (base64url encoded).
 */
export function serializeAttestationCredential(credential) {
	return {
		id: credential.id,
		rawId: bufferToBase64Url(credential.rawId),
		type: credential.type,
		clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
		attestationObject: bufferToBase64Url(credential.response.attestationObject)
	};
}

/**
 * Serialize a credential returned from navigator.credentials.get
 * into a payload suitable for the backend (base64url encoded).
 */
export function serializeAssertionCredential(credential) {
	return {
		id: credential.id,
		rawId: bufferToBase64Url(credential.rawId),
		type: credential.type,
		clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
		authenticatorData: bufferToBase64Url(credential.response.authenticatorData),
		signature: bufferToBase64Url(credential.response.signature),
		userHandle: credential.response.userHandle
			? bufferToBase64Url(credential.response.userHandle)
			: null
	};
}

export function supported() {
	return (
		typeof window !== "undefined" &&
		typeof window.PublicKeyCredential === "function" &&
		"credentials" in navigator &&
		typeof navigator.credentials.get === "function"
	);
}
