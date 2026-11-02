import type { PanelResponse } from "@/panel/request";

/**
 * Creates a request and its failed response,
 * e.g. as options for a `RequestError`
 */
export function failed(response: Partial<PanelResponse> = {}): {
	request: Request;
	response: PanelResponse;
} {
	const url = response.url ?? "https://example.com/api";

	return {
		request: new Request(url),
		response: {
			headers: new Headers(),
			json: {},
			ok: false,
			status: 500,
			statusText: "Internal Server Error",
			text: "",
			url,
			...response
		}
	};
}
