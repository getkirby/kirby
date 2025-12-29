/**
 * WebAuthn helper utilities shared between registration and login flows.
 *
 * Minimal conversions to move between base64url strings and ArrayBuffers
 * for the WebAuthn APIs and our backend.
 */

function b64ToBuffer(str) {
	if (typeof str !== "string") {
		return str;
	}

	const RFC_PREFIX = "=?BINARY?B?";
	const RFC_SUFFIX = "?=";

	if (str.startsWith(RFC_PREFIX) && str.endsWith(RFC_SUFFIX)) {
		str = str.slice(RFC_PREFIX.length, -RFC_SUFFIX.length);
	}

	const normalized =
		str.replace(/-/g, "+").replace(/_/g, "/") +
		"=".repeat((4 - (str.length % 4)) % 4);

	return Uint8Array.from(atob(normalized), (char) => char.charCodeAt(0)).buffer;
}

function bufferToB64(buffer) {
	if (buffer === null || buffer === undefined) {
		return null;
	}

	return btoa(String.fromCharCode(...new Uint8Array(buffer)))
		.replace(/\+/g, "-")
		.replace(/\//g, "_")
		.replace(/=+$/, "");
}

export function normalizePublicKey(options) {
	const publicKey = options.publicKey ?? options;

	if (publicKey.challenge) {
		publicKey.challenge = b64ToBuffer(publicKey.challenge);
	}

	if (publicKey.user?.id) {
		publicKey.user.id = b64ToBuffer(publicKey.user.id);
	}

	if (Array.isArray(publicKey.excludeCredentials)) {
		publicKey.excludeCredentials = publicKey.excludeCredentials
			.filter((item) => item && typeof item.id === "string")
			.map((item) => ({ ...item, id: b64ToBuffer(item.id) }));
	}

	if (Array.isArray(publicKey.allowCredentials)) {
		publicKey.allowCredentials = publicKey.allowCredentials
			.filter((item) => item && typeof item.id === "string")
			.map((item) => ({
				...item,
				id: b64ToBuffer(item.id)
			}));
	}

	return { publicKey };
}

export function serializeAttestation(credential) {
	return {
		id: credential.id,
		rawId: bufferToB64(credential.rawId),
		type: credential.type,
		clientDataJSON: bufferToB64(credential.response.clientDataJSON),
		attestationObject: bufferToB64(credential.response.attestationObject)
	};
}

export function serializeAssertion(credential) {
	return {
		id: credential.id,
		rawId: bufferToB64(credential.rawId),
		type: credential.type,
		clientDataJSON: bufferToB64(credential.response.clientDataJSON),
		authenticatorData: bufferToB64(credential.response.authenticatorData),
		signature: bufferToB64(credential.response.signature),
		userHandle: credential.response.userHandle
			? bufferToB64(credential.response.userHandle)
			: null
	};
}

/**
 * Checks whether passkeys are supported by the browser
 */
export function supported() {
	return (
		typeof window !== "undefined" &&
		typeof window.PublicKeyCredential === "function" &&
		"credentials" in navigator &&
		typeof navigator.credentials.get === "function"
	);
}
