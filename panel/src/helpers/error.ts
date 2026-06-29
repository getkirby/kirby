/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

export function isAbortError(error: unknown): error is Error {
	return error instanceof Error && error.name === "AbortError";
}
