import type { HttpResponse } from "@/panel/request";

/**
 * Signals a failed request to a third-party URL.
 * Its response never picks the message or the Panel's error dialog.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default class ExternalRequestError extends Error {
	request: Request;
	response: HttpResponse;

	constructor(
		message: string,
		{
			request,
			response
		}: {
			request: Request;
			response: HttpResponse;
		}
	) {
		super(message);
		this.request = request;
		this.response = response;
	}
}
