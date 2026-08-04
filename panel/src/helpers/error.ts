/**
 * Whether the error has been caused by an aborted request
 * @since 5.6.0
 */
export function isAbortError(error: unknown): error is Error {
	return error instanceof Error && error.name === "AbortError";
}
