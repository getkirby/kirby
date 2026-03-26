/**
 * Returns the base URL from the <base> element
 * @since 4.0.0
 */
export function base(): URL {
	return new URL(
		document.querySelector("base")?.href ?? window.location.origin
	);
}

/**
 * Turns the given object into an URL query string
 * and appends it, if given, to the query of the origin
 * @since 4.0.0
 */
export function buildQuery(
	query: Record<string, string | null> = {},
	origin: string | Record<string, string> | URL = {}
): URLSearchParams {
	const search = origin instanceof URL ? origin.search : origin;
	const params = new URLSearchParams(search);

	// add all data params unless they are empty/null
	for (const [key, value] of Object.entries(query)) {
		if (value !== null) {
			params.set(key, value);
		}
	}

	return params;
}

/**
 * Builds a full URL object based on the
 * given path or another URL object and query data
 * @since 4.0.0
 */
export function buildUrl(
	url: string | URL = "",
	query: Record<string, string | null> = {},
	origin?: string | URL
): URL {
	const result = toObject(url, origin);
	result.search = String(buildQuery(query, result.search));
	return result;
}

/**
 * Checks if the url string is absolute
 * @since 4.0.0
 */
export function isAbsolute(url: unknown): boolean {
	return String(url).match(/^https?:\/\//) !== null;
}

/**
 * Checks if the url is on the same origin
 * @since 4.0.0
 */
export function isSameOrigin(url: string | URL): boolean {
	return toObject(url).origin === window.location.origin;
}

/**
 * Checks if the given argument is a URL
 * @since 4.0.0
 *
 * @param strict - Whether to also check the URL against Kirby's URL validator
 */
export function isUrl(
	url: unknown,
	strict: boolean = false
): url is URL | Location | string {
	let normalized: string;

	if (url instanceof URL || url instanceof Location) {
		normalized = url.toString();
	} else if (typeof url === "string") {
		normalized = url;
	} else {
		return false;
	}

	// check if the given URL can be
	// converted to a URL object to
	// validate it
	try {
		new URL(normalized, window.location.href);
	} catch {
		return false;
	}

	// in strict mode, also validate against the
	// URL regex from the backend URL validator
	if (strict === true) {
		const regex =
			/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:localhost)|(?:[a-z0-9\u00a1-\uffff](?:[a-z0-9\u00a1-\uffff_-]{0,62}[a-z0-9\u00a1-\uffff])?\.)+(?:[a-z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:[/?#]\S*)?$/i;
		return regex.test(normalized);
	}

	return true;
}

/**
 * Make sure the URL is absolute
 * @since 4.0.0
 */
export function makeAbsolute(
	path: string | URL,
	origin?: string | URL
): string {
	if (isAbsolute(path) === true) {
		return String(path);
	}

	const originStr = String(origin ?? base()).replaceAll(/\/$/g, "");
	const pathStr = String(path).replaceAll(/^\//g, "");

	return originStr + "/" + pathStr;
}

/**
 * Converts any given url to a URL object
 * @since 4.0.0
 */
export function toObject(url: string | URL, origin?: string | URL): URL {
	return url instanceof URL ? url : new URL(makeAbsolute(String(url), origin));
}

export default {
	base,
	buildQuery,
	buildUrl,
	isAbsolute,
	isSameOrigin,
	isUrl,
	makeAbsolute,
	toObject
};
