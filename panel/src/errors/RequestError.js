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

	dialog() {
		const state = this.state();

		if (state.exception === "Kirby\\Exception\\FormValidationException") {
			return {
				component: "k-validation-error-dialog",
				props: {
					message: this.message,
					fields: state.details
				}
			};
		}

		return {
			component: "k-request-error-dialog",
			props: {
				message: this.message,
				request: {
					url: this.request.url,
					method: this.request.method
				},
				response: {
					status: this.response.status
				},
				exception: {
					file: state.file,
					line: state.line,
					type: state.exception,
					url: state.editor
				},
				details: state.details,
				trace: state.trace
			}
		};
	}

	state() {
		return this.response.json;
	}
}
