import { buildUrl, isSameOrigin, makeAbsolute } from "@/helpers/url";
import { toLowerKeys } from "../helpers/object";
import AuthError from "@/errors/AuthError.js";
import JsonRequestError from "@/errors/JsonRequestError.js";
import OfflineError from "@/errors/OfflineError.js";
import RedirectError from "@/errors/RedirectError.js";
import RequestError from "@/errors/RequestError.js";

export interface PanelResponse {
	headers: Headers;
	json: Record<string, unknown>;
	ok: boolean;
	status: number;
	statusText: string;
	text: string;
	url: string;
}

export type RequestBody =
	| string
	| FormData
	| HTMLFormElement
	| Record<string, unknown>
	| null;

export interface PanelRequestOptions extends Omit<
	RequestInit,
	"body" | "headers" | "referrer"
> {
	body: RequestBody;
	csrf?: string;
	globals?: string | string[];
	headers: Record<string, string>;
	query: Record<string, string | null>;
	referrer?: string;
}

/**
 * Creates a proper request body
 * @since 4.0.0
 */
export function body(data: RequestBody | undefined): string | null | undefined {
	if (data instanceof HTMLFormElement) {
		data = new FormData(data);
	}

	if (data instanceof FormData) {
		data = Object.fromEntries(data);
	}

	if (typeof data === "object" && data !== null) {
		return JSON.stringify(data);
	}

	return data;
}

/**
 * Convert globals to comma separated string
 * @since 4.0.0
 */
export function globals(input?: string | string[]): string | undefined {
	if (Array.isArray(input) === true) {
		return input.length ? input.join(",") : undefined;
	}

	return input || undefined;
}

/**
 * Builds all required headers for a request
 * @since 4.0.0
 */
export function headers(
	input: Record<string, string> = {},
	options: Partial<PanelRequestOptions> = {}
): Record<string, string> {
	const result: Record<string, string> = {
		"content-type": "application/json",
		"x-fiber": "true",
		...toLowerKeys(input)
	};

	if (options.csrf) {
		result["x-csrf"] = options.csrf;
	}

	const globalsHeader = globals(options.globals);

	if (globalsHeader) {
		result["x-fiber-globals"] = globalsHeader;
	}

	if (options.referrer) {
		result["x-fiber-referrer"] = options.referrer;
	}

	return result;
}

/**
 * @since 4.0.0
 */
export function redirect(url: string | URL): never {
	throw new RedirectError(makeAbsolute(url));
}

/**
 * Sends a Panel request to the backend with
 * all the right headers and other options.
 *
 * It also makes sure to redirect requests,
 * which cannot be handled via fetch and
 * throws more useful errors.
 * @since 4.0.0
 */
export async function request(
	url: string,
	options: Partial<PanelRequestOptions> = {}
): Promise<{ request: Request; response: PanelResponse }> {
	// extract Request options from options
	const { csrf, globals, referrer, query, ...rest } = options;

	// merge with a few defaults
	const init: RequestInit = {
		cache: "no-store",
		credentials: "same-origin",
		mode: "same-origin",
		...rest,
		body: body(options.body),
		headers: headers(options.headers, options)
	};

	// The request object is a nice way to access all the
	// important parts later in errors for example
	const req = new Request(buildUrl(url, query), init);

	// Don't even try to request a
	// cross-origin url. Redirect instead.
	if (isSameOrigin(req.url) === false) {
		return redirect(req.url);
	}

	// parse the JSON response and react on errors
	return await responder(req, await safeFetch(req));
}

/**
 * Try to parse the response and throw
 * matching errors for issues with the response.
 * @since 4.0.0
 */
export async function responder(
	request: Request,
	raw: Response
): Promise<{ request: Request; response: PanelResponse }> {
	const type = raw.headers.get("Content-Type");

	// redirect to non-json requests
	if (type?.includes("application/json") === false) {
		return redirect(raw.url);
	}

	const response: PanelResponse = {
		headers: raw.headers,
		json: {},
		ok: raw.ok,
		status: raw.status,
		statusText: raw.statusText,
		text: "",
		url: raw.url
	};

	try {
		response.text = await raw.text();
		response.json = JSON.parse(response.text);
	} catch (error) {
		throw new JsonRequestError("Invalid JSON response", {
			cause: error,
			request,
			response
		});
	}

	// auth error
	if (response.status === 401) {
		throw new AuthError(`Unauthenticated`, {
			request,
			response
		});
	}

	// request error
	if (response.ok === false) {
		throw new RequestError(`The request to ${response.url} failed`, {
			request,
			response
		});
	}

	return {
		request,
		response
	};
}

/**
 * Fetches a request and converts network errors
 * into a Panel offline state
 */
export async function safeFetch(request: Request): Promise<Response> {
	try {
		return await fetch(request);
	} catch (error) {
		if (error instanceof Error && error.name === "AbortError") {
			throw error;
		}

		// @ts-expect-error - remove once Panel type exists (TODO)
		window.panel?.events?.emit("offline", error);

		throw new OfflineError("Panel is offline", {
			cause: error,
			request
		});
	}
}

export default request;
