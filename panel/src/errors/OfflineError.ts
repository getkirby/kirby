/**
 * Signals a network failure.
 * Handled by panel.error() which sets the panel's offline state.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since    5.0.3
 */
export default class OfflineError extends Error {
	request: Request;

	constructor(
		message: string,
		{
			request,
			cause
		}: {
			request: Request;
			cause?: unknown;
		}
	) {
		super(message, { cause });
		this.request = request;
	}
}
