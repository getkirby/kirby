/**
 * Returns the base URL from the <base> element
 * @returns {URL}
 */
export function base() {
	return new URL(
		document.querySelector("base")?.href ?? window.location.origin
	);
}

/**
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
	Object.entries(query).forEach(([key, value]) => {
		if (value !== null) {
			params.set(key, value);
		}
	});

	return params;
}

/**
 * Builds a full URL object based on the
 * given path or another URL object and query data
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
 *
 * @param {string} url
 * @returns {boolean}
 */
export function isAbsolute(url) {
	return String(url).match(/^https?:\/\//) !== null;
}

/**
 * Checks if the url is on the same origin
 *
 * @param {string} url
 * @returns {boolean}
 */
export function isSameOrigin(url) {
	return toObject(url).origin === window.location.origin;
}

/**
 * Checks if the given argument is a URL
 *
 * @param {string|URL} url
 * @returns {boolean}
 */
export function isUrl(url) {
	if (url instanceof URL || url instanceof Location) {
		return true;
	}

	if (typeof url !== "string") {
		return false;
	}

	// check if the given URL can be
	// converted to a URL object to
	// validate it
	try {
		new URL(url, window.location);
		return true;
	} catch (error) {
		return false;
	}
}

/**
 * Make sure the URL is absolute
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
	buildUrl,
	buildQuery,
	isAbsolute,
	isSameOrigin,
	isUrl,
	makeAbsolute,
	toObject
};
