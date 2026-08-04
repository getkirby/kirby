import type { PanelResponse } from "@/panel/request";

/**
 * Stores the request and response in the error
 * object to allow for more detailed error inspection
 * @since 4.0.0
 */
export default class RequestError extends Error {
	details: unknown;
	key: string | undefined;
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

		this.details = response.json.details;
		this.key = response.json.key as string | undefined;
		this.request = request;
		this.response = response;
	}

	state() {
		return this.response.json;
	}
}
