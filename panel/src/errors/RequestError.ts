import type { PanelResponse } from "@/panel/request";

/**
 * Stores the request and response in the error
 * object to allow for more detailed error inspection
 * @since 4.0.0
 */
export default class RequestError extends Error {
	key: string | undefined;
	details: unknown;
	request: Request;
	response: PanelResponse;

	constructor(
		message: string,
		{
			request,
			response,
			cause
		}: {
			request: Request;
			response: PanelResponse;
			cause?: unknown;
		}
	) {
		super((response.json.message ?? response.json.error ?? message) as string, {
			cause
		});

		this.request = request;
		this.response = response;
		this.details = response.json.details;
		this.key = response.json.key as string | undefined;
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
