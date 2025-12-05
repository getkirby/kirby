/**
 * Stores the request and response in the error
 * object to allow for more detailed error inspection
 * @since 4.0.0
 */
export default class RequestError extends Error {
	constructor(message, { request, response, cause }) {
		super(response.json.message ?? response.json.error ?? message, { cause });

		this.request = request;
		this.response = response;
		this.details = response.json.details;
	}

	component() {
		const components = {
			"Kirby\\Exception\\ValidationException": "k-validation-error-dialog"
		};

		return components[this.exception()] ?? "k-request-error-dialog";
	}

	exception() {
		return this.state().exception;
	}

	state() {
		return this.response.json;
	}
}
