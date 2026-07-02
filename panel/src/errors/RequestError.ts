import type { PanelResponse } from "@/panel/request";

/**
 * Stores the request and response in the error
 * object to allow for more detailed error inspection
 * @since 4.0.0
 */
export default class RequestError extends Error {
	request: Request;
	response: PanelResponse;
	details: unknown;

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
	}

	state() {
		return this.response.json;
	}
}
