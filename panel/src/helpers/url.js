/**
 * Returns the base URL from the <base> element
 *
 * @since 4.0.0
 * @returns {URL}
 */
export function base() {
	return new URL(
		document.querySelector("base")?.href ?? window.location.origin
	);
}

/**
 * Turns the given object into an URL query string
 * and appends it, if given, to the query of the origin
 *
 * @since 4.0.0
 * @param {object} query
 * @param {string|URL} origin
 * @returns {string}
 */
export function buildQuery(query = {}, origin = {}) {
	if (origin instanceof URL) {
		origin = origin.search;
	}

	const params = new URLSearchParams(origin);

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
 *
 * @param {string|URL} url
 * @param {Object} query
 * @param {string|URL} origin
 * @returns {URL}
 */
export function buildUrl(url = "", query = {}, origin) {
	url = toObject(url, origin);
	url.search = buildQuery(query, url.search);

	return url;
}

/**
 * Checks if the url string is absolute
 * @since 4.0.0
 *
 * @param {string} url
 * @returns {boolean}
 */
export function isAbsolute(url) {
	return String(url).match(/^https?:\/\//) !== null;
}

/**
 * Checks if the url is on the same origin
 * @since 4.0.0
 *
 * @param {string} url
 * @returns {boolean}
 */
export function isSameOrigin(url) {
	return toObject(url).origin === window.location.origin;
}

/**
 * Checks if the given argument is a URL
 * @since 4.0.0
 *
 * @param {string|URL} url
 * @param {boolean} strict Whether to also check the URL against Kirby's URL validator
 * @returns {boolean}
 */
export function isUrl(url, strict) {
	if (url instanceof URL || url instanceof Location) {
		url = url.toString();
	}

	if (typeof url !== "string") {
		return false;
	}

	// check if the given URL can be
	// converted to a URL object to
	// validate it
	try {
		new URL(url, window.location);
	} catch {
		return false;
	}

	// in strict mode, also validate against the
	// URL regex from the backend URL validator
	if (strict === true) {
		const regex =
			/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z0-9\u00a1-\uffff][a-z0-9\u00a1-\uffff_-]{0,62})?[a-z0-9\u00a1-\uffff]\.)+(?:[a-z\u00a1-\uffff]{2,}\.?))(?::\d{2,5})?(?:[/?#]\S*)?$/i;
		return regex.test(url);
	}

	return true;
}

/**
 * Make sure the URL is absolute
 * @since 4.0.0
 *
 * @param {string} path
 * @param {string|URL} origin
 * @returns {string}
 */
export function makeAbsolute(path, origin) {
	if (isAbsolute(path) === true) {
		return path;
	}

	origin = origin ?? base();
	origin = String(origin).replaceAll(/\/$/g, "");
	path = String(path).replaceAll(/^\//g, "");

	return origin + "/" + path;
}

/**
 * Converts any given url to a URL object
 * @since 4.0.0
 *
 * @param {string|URL} url
 * @param {string|URL} origin
 * @returns {URL}
 */
export function toObject(url, origin) {
	return url instanceof URL ? url : new URL(makeAbsolute(url, origin));
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
