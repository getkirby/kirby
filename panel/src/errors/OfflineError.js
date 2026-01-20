/**
 * Signals a network failure while the Panel is offline
 * @since 5.0.3
 */
export default class OfflineError extends Error {
	constructor(message, { request, cause } = {}) {
		super(message, { cause });

		this.request = request;
	}
}
