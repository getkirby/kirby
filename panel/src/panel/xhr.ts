/**
 * Fallback for fetch() based on XMLHttpRequest
 * to report the upload progress
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */

import { isSameOrigin } from "@/helpers/url";

/**
 * Converts a finished XMLHttpRequest into
 * the Response that fetch() would resolve with
 */
function response(xhr: XMLHttpRequest): Response {
	const headers = new Headers();

	for (const line of xhr.getAllResponseHeaders().split("\r\n")) {
		const index = line.indexOf(":");

		if (index > 0) {
			headers.append(line.slice(0, index), line.slice(index + 1).trim());
		}
	}

	// empty responses, e.g. a 204, must not have a body
	const body = xhr.response.size > 0 ? xhr.response : null;
	const response = new Response(body, {
		headers,
		status: xhr.status,
		statusText: xhr.statusText
	});

	// a constructed response has no URL of its own
	return Object.defineProperty(response, "url", { value: xhr.responseURL });
}

/**
 * Sends a request with XMLHttpRequest, which unlike
 * fetch() reports the upload progress, and resolves
 * with a Response like fetch() does
 */
export async function send(
	request: Request,
	progress: (percent: number) => void
): Promise<Response> {
	// like with fetch(), same-origin requests must not
	// leave the origin, not even through a redirect
	function leaves(url: string): boolean {
		return request.mode === "same-origin" && isSameOrigin(url) === false;
	}

	if (leaves(request.url) === true) {
		throw new TypeError("Failed to fetch");
	}

	const { promise, resolve, reject } = Promise.withResolvers<Response>();
	const xhr = new XMLHttpRequest();

	xhr.open(request.method, request.url);
	xhr.responseType = "blob";

	request.headers.forEach((value, name) => {
		xhr.setRequestHeader(name, value);
	});

	xhr.upload.addEventListener("progress", (event) => {
		if (event.lengthComputable === true) {
			progress(Math.round((event.loaded / event.total) * 100));
		}
	});

	xhr.addEventListener("load", () => {
		// XMLHttpRequest follows redirects to other origins,
		// but their responses must never be used
		if (leaves(xhr.responseURL) === true) {
			reject(new TypeError("Failed to fetch"));
			return;
		}

		resolve(response(xhr));
	});

	// fetch() rejects network errors with a TypeError as well
	xhr.addEventListener("error", () => {
		reject(new TypeError("Failed to fetch"));
	});

	const body = await request.blob();

	request.signal.throwIfAborted();
	request.signal.addEventListener("abort", () => {
		xhr.abort();
		reject(request.signal.reason);
	});

	xhr.send(body);

	return promise;
}
