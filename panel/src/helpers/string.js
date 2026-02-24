import { DOMParser, DOMSerializer, Schema } from "prosemirror-model";

import "./regex";
import { createMarks, createNodes } from "./writer";

const escapingMap = {
	"&": "&amp;",
	"<": "&lt;",
	">": "&gt;",
	'"': "&quot;",
	"'": "&#039;",
	"/": "&#x2F;",
	"`": "&#x60;",
	"=": "&#x3D;"
};

/**
 * Converts camel-case to kebab-case
 * @param {string} string
 * @returns {string}
 */
export function camelToKebab(string) {
	return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
}

/**
 * Escapes HTML in string
 * @param {string} string
 * @returns {string}
 *
 * Source: https://github.com/janl/mustache.js/blob/v4.2.0/mustache.js#L60-L75
 *
 * The MIT License
 *
 * Copyright (c) 2009 Chris Wanstrath (Ruby)
 * Copyright (c) 2010-2014 Jan Lehnardt (JavaScript)
 * Copyright (c) 2010-2015 The mustache.js community
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
export function escapeHTML(string) {
	return String(string).replace(/[&<>"'`=/]/g, (char) => escapingMap[char]);
}

/**
 * Checks if string contains an emoji
 * @param {string} string
 * @returns {bool}
 */
export function hasEmoji(string) {
	if (typeof string !== "string") {
		return false;
	}

	// skip if string has no valid emoji at all
	if (/^[a-z0-9_-]+$/.test(string) === true) {
		return false;
	}

	return /\p{Emoji}/u.test(string);
}

/**
 * Checks if a string is empty
 * @since 4.0.0
 * @param {String|undefined|null} string
 * @returns {Boolean}
 */
export function isEmpty(string) {
	if (!string) {
		return true;
	}

	return String(string).length === 0;
}

/**
 * Turns first letter lowercase
 * @param {string} string
 * @returns {string}
 */
export function lcfirst(string) {
	const str = String(string);
	return str.charAt(0).toLowerCase() + str.slice(1);
}

/**
 * Trims the given character(s) at the beginning of the string.
 * This method is greedy and removes any occurrence at the beginning,
 * not just the first.
 *
 * @param {string} string
 * @param {string} replace
 * @returns {string}
 */
export function ltrim(string = "", replace = "") {
	const expression = new RegExp(`^(${RegExp.escape(replace)})+`, "g");
	return string.replace(expression, "");
}

/**
 * Prefixes string with 0 until length is reached
 * @param {string} value
 * @param {number} length
 * @returns
 */
export function pad(value, length = 2) {
	value = String(value);
	let pad = "";

	while (pad.length < length - value.length) {
		pad += "0";
	}

	return pad + value;
}

/**
 * Generate random alpha-num string of specified length
 * @param {number} length
 * @returns {string}
 */
export function random(length) {
	let result = "";
	const pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	const count = pool.length;
	for (var i = 0; i < length; i++) {
		result += pool.charAt(Math.floor(Math.random() * count));
	}
	return result;
}

/**
 * Trims the given characters at the end of the string
 * This method is greedy and removes any occurrence at the end,
 * not just the last.
 *
 * @param {string} string
 * @param {string} replace
 * @returns {string}
 */
export function rtrim(string = "", replace = "") {
	const expression = new RegExp(`(${RegExp.escape(replace)})+$`, "g");
	return string.replace(expression, "");
}

/**
 * Sanitizes HTML by only keeping allowed marks
 * (bold, italic, underline, links)
 * @param {string} html
 * @param {object} options
 * @returns {string}
 */
export function sanitizeHTML(html, options = {}) {
	if (!html) {
		return "";
	}

	const marks = createMarks(
		options.marks ?? [
			"bold",
			"code",
			"italic",
			"link",
			"strike",
			"sub",
			"sup",
			"underline"
		]
	);

	const nodes = createNodes(
		options.nodes ?? { doc: { inline: true }, text: true },
		["doc", "text", "paragraph"]
	);

	// Build schema from the extracted definitions
	const sanitizeSchema = new Schema({
		marks: Object.fromEntries(
			Object.values(marks).map((m) => [m.name, m.schema])
		),
		nodes: Object.fromEntries(
			Object.values(nodes).map((n) => [n.name, n.schema])
		)
	});

	const dom = new window.DOMParser().parseFromString(
		`<div>${html}</div>`,
		"text/html"
	).body.firstElementChild;

	const doc = DOMParser.fromSchema(sanitizeSchema).parse(dom);
	const div = document.createElement("div");
	div.appendChild(
		DOMSerializer.fromSchema(sanitizeSchema).serializeFragment(doc.content)
	);

	return div.innerHTML;
}

/**
 * Convert string to ASCII slug
 * @param {string} string string to be converted
 * @param {array} rules ruleset to convert non-ASCII characters
 * @param {string} allowed list of allowed characters (default: a-z0-9)
 * @param {string} separator character used to replace non-allowed characters
 * @returns {string}
 */
export function slug(string, rules = [], allowed = "", separator = "-") {
	if (!string) {
		return "";
	}

	if (!allowed || allowed.length === 0) {
		allowed = "a-z0-9";
	}

	string = string.trim().toLowerCase();

	// replace according to language and ascii rules
	for (const ruleset of rules) {
		for (const rule in ruleset) {
			const isTrimmed = rule.slice(0, 1) !== "/";
			const trimmed = rule.slice(1, rule.length - 1);
			const regex = isTrimmed ? rule : trimmed;
			string = string.replace(
				new RegExp(RegExp.escape(regex), "g"),
				ruleset[rule]
			);
		}
	}

	// remove all other non-ASCII characters
	string = string.replace("/[^\x09\x0A\x0D\x20-\x7E]/", "");

	// replace non-allowed characters (e.g. spaces) with separator
	string = string.replace(new RegExp("[^" + allowed + "]", "ig"), separator);

	// remove double separators
	string = string.replace(
		new RegExp("[" + RegExp.escape(separator) + "]{2,}", "g"),
		separator
	);

	// replace slashes with dashes
	string = string.replace("/", separator);

	// trim leading and trailing non-word-chars
	string = string.replace(new RegExp("^[^a-z0-9]+", "g"), "");
	string = string.replace(new RegExp("[^a-z0-9]+$", "g"), "");

	return string;
}

/**
 * Strips HTML tags from string
 * @param {string} string
 * @returns {string}
 */
export function stripHTML(string) {
	return String(string).replace(/(<([^>]+)>)/gi, "");
}

/**
 * Replaces template placeholders in string with provided values
 * @param {string} string
 * @param {Object} values
 * @returns {string}
 */
export function template(string, values = {}) {
	const resolve = (parts, data = {}) => {
		const part = escapeHTML(parts.shift());
		const value = data[part] ?? "…";

		if (value === "…" || parts.length === 0) {
			return value;
		}

		return resolve(parts, value);
	};

	const opening = "[{]{1,2}[\\s]?";
	const closing = "[\\s]?[}]{1,2}";

	string = string.replace(
		new RegExp(`${opening}(.*?)${closing}`, "gi"),
		($0, $1) => resolve($1.split("."), values)
	);

	return string.replace(new RegExp(`${opening}.*${closing}`, "gi"), "…");
}

/**
 * Turns first letter uppercase
 * @param {string} string
 * @returns {string}
 */
export function ucfirst(string) {
	const str = String(string);
	return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Turns first letter of each word uppercase
 * @param {string} string
 * @returns {string}
 */
export function ucwords(string) {
	return String(string)
		.split(/ /g)
		.map((word) => ucfirst(word))
		.join(" ");
}

/**
 * Turns escaped HTML entities into actual characters again
 * @param {string} string
 * @returns  {string}
 */
export function unescapeHTML(string) {
	for (const symbol in escapingMap) {
		string = String(string).replaceAll(escapingMap[symbol], symbol);
	}
	return string;
}

/**
 * Returns a unique ID
 * @returns {string}
 */
export function uuid() {
	let uuid = "",
		i,
		random;
	for (i = 0; i < 32; i++) {
		random = (Math.random() * 16) | 0;

		if (i == 8 || i == 12 || i == 16 || i == 20) {
			uuid += "-";
		}
		uuid += (i == 12 ? 4 : i == 16 ? (random & 3) | 8 : random).toString(16);
	}
	return uuid;
}

export default {
	camelToKebab,
	escapeHTML,
	hasEmoji,
	isEmpty,
	lcfirst,
	ltrim,
	pad,
	random,
	rtrim,
	sanitizeHTML,
	slug,
	stripHTML,
	template,
	ucfirst,
	ucwords,
	unescapeHTML,
	uuid
};
