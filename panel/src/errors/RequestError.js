/**
 * Stores the request and response in the error
 * object to allow for more detailed error inspection
 * @since 4.0.0
 */
export default class RequestError extends Error {
	constructor(message, { request, response, cause }) {
		super(response.json.message ?? message, { cause });

		this.request = request;
		this.response = response;
	}

	state() {
		return this.response.json;
	}
}
