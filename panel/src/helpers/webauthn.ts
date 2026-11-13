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

/**
 * Creates a new passkey: parses the server options, prompts the
 * authenticator through `navigator.credentials.create()` and hands
 * the credential's JSON to `onComplete`. Any failure is mapped
 * and forwarded to `onError`.
 *
 * @example
 * await create(publicKey, (attestation) => save(attestation), notify);
 */
async function create(
	publicKey: PublicKeyCredentialCreationOptionsJSON,
	onComplete: (attestation: RegistrationResponseJSON) => unknown,
	onError?: (message: string) => void
): Promise<void> {
	try {
		const credential = await navigator.credentials.create({
			publicKey: PublicKeyCredential.parseCreationOptionsFromJSON(publicKey)
		});

		await onComplete(
			(credential as PublicKeyCredential).toJSON() as RegistrationResponseJSON
		);
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
 * Verifies an existing passkey: parses the server options, prompts the
 * authenticator through `navigator.credentials.get()` and hands the
 * credential's JSON to `onComplete`. Any failure is mapped and
 * forwarded to `onError`.
 *
 * @example
 * await get(publicKey, (assertion) => authorize(assertion), notify);
 */
async function get(
	publicKey: PublicKeyCredentialRequestOptionsJSON,
	onComplete: (assertion: AuthenticationResponseJSON) => unknown,
	onError?: (message: string) => void
): Promise<void> {
	try {
		const credential = await navigator.credentials.get({
			publicKey: PublicKeyCredential.parseRequestOptionsFromJSON(publicKey)
		});

		await onComplete(
			(credential as PublicKeyCredential).toJSON() as AuthenticationResponseJSON
		);
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

export default { create, get, isSupported };
