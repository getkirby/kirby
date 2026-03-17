import { DOMParser, DOMSerializer, Schema } from "prosemirror-model";

import "./regex";
import { createMarks, createNodes } from "./writer";
import type WriterMark from "@/components/Forms/Writer/Mark";
import type WriterNode from "@/components/Forms/Writer/Node";

const escapingMap: Record<string, string> = {
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
 *
 * @example
 * camelToKebab("myString") // "my-string"
 */
export function camelToKebab(string: unknown): string {
	return String(string)
		.replace(/([a-z0-9])([A-Z])/g, "$1-$2")
		.toLowerCase();
}

/**
 * Escapes HTML in string
 *
 * @example
 * escapeHTML('<b>bold</b>') // "&lt;b&gt;bold&lt;&#x2F;b&gt;"
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
export function escapeHTML(string: unknown): string {
	return String(string).replace(/[&<>"'`=/]/g, (char) => escapingMap[char]);
}

/**
 * Checks if string contains an emoji
 *
 * @example
 * hasEmoji("Hello 👋") // true
 * hasEmoji("Hello") // false
 */
export function hasEmoji(string: unknown): boolean {
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
 *
 * @example
 * isEmpty("") // true
 * isEmpty("hello") // false
 * isEmpty(null) // true
 *
 * @since 4.0.0
 */
export function isEmpty(string: unknown): boolean {
	if (!string) {
		return true;
	}

	return String(string).length === 0;
}

/**
 * Turns first letter lowercase
 *
 * @example
 * lcfirst("Hello World") // "hello World"
 */
export function lcfirst(string: unknown): string {
	const str = String(string);
	return str.charAt(0).toLowerCase() + str.slice(1);
}

/**
 * Trims the given character(s) at the beginning of the string.
 * This method is greedy and removes any occurrence at the beginning,
 * not just the first.
 *
 * @example
 * ltrim("//path/to/file", "/") // "path/to/file"
 */
export function ltrim(string: unknown = "", replace: string = ""): string {
	const expression = new RegExp(`^(${RegExp.escape(replace)})+`, "g");
	return String(string).replace(expression, "");
}

/**
 * Prefixes string with 0 until length is reached
 *
 * @example
 * pad(5) // "05"
 * pad(5, 3) // "005"
 * pad(42) // "42"
 */
export function pad(value: unknown, length: number = 2): string {
	const string = String(value);
	let pad = "";

	while (pad.length < length - string.length) {
		pad += "0";
	}

	return pad + string;
}

/**
 * Generate random alpha-num string of specified length
 *
 * @example
 * random(8) // "aB3xK9mZ" (random result)
 */
export function random(length: number): string {
	let result = "";
	const pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	const count = pool.length;

	for (let i = 0; i < length; i++) {
		result += pool.charAt(Math.floor(Math.random() * count));
	}

	return result;
}

/**
 * Trims the given characters at the end of the string
 * This method is greedy and removes any occurrence at the end,
 * not just the last.
 *
 * @example
 * rtrim("path/to/file//", "/") // "path/to/file"
 */
export function rtrim(string: unknown = "", replace: string = ""): string {
	const expression = new RegExp(`(${RegExp.escape(replace)})+$`, "g");
	return String(string).replace(expression, "");
}

/**
 * Sanitizes HTML by only keeping allowed marks
 * (bold, italic, underline, links)
 *
 * @example
 * sanitizeHTML("<b>bold</b> <script>alert(1)</script>") // "<strong>bold</strong> "
 * sanitizeHTML("<b>bold</b>", { marks: ["italic"] }) // "bold"
 */
export function sanitizeHTML(
	html: unknown,
	options: {
		marks?: (string | WriterMark)[];
		nodes?: (string | WriterNode)[];
	} = {}
): string {
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
	).body.firstElementChild as HTMLElement;

	const doc = DOMParser.fromSchema(sanitizeSchema).parse(dom);
	const div = document.createElement("div");
	div.appendChild(
		DOMSerializer.fromSchema(sanitizeSchema).serializeFragment(doc.content)
	);

	return div.innerHTML;
}

/**
 * Convert string to ASCII slug
 *
 * @example
 * slug("Hello World") // "hello-world"
 * slug("Hello World", [], "a-z0-9", "_") // "hello_world"
 *
 * @param string - string to be converted
 * @param rules - ruleset to convert non-ASCII characters
 * @param allowed - list of allowed characters (default: a-z0-9)
 * @param separator - character used to replace non-allowed characters
 */
export function slug(
	string: unknown,
	rules: Record<string, string>[] = [],
	allowed: string = "",
	separator: string = "-"
): string {
	if (!string) {
		return "";
	}

	if (!allowed || allowed.length === 0) {
		allowed = "a-z0-9";
	}

	let str = String(string).trim().toLowerCase();

	// replace according to language and ascii rules
	for (const ruleset of rules) {
		for (const rule in ruleset) {
			const isTrimmed = rule.slice(0, 1) !== "/";
			const trimmed = rule.slice(1, rule.length - 1);
			const regex = isTrimmed ? rule : trimmed;
			str = str.replace(new RegExp(RegExp.escape(regex), "g"), ruleset[rule]);
		}
	}

	// remove all other non-ASCII characters
	str = str.replace("/[^\x09\x0A\x0D\x20-\x7E]/", "");

	// replace non-allowed characters (e.g. spaces) with separator
	str = str.replace(new RegExp("[^" + allowed + "]", "ig"), separator);

	// remove double separators
	str = str.replace(
		new RegExp("[" + RegExp.escape(separator) + "]{2,}", "g"),
		separator
	);

	// replace slashes with dashes
	str = str.replace("/", separator);

	// trim leading and trailing non-word-chars
	str = str.replace(new RegExp("^[^a-z0-9]+", "g"), "");
	str = str.replace(new RegExp("[^a-z0-9]+$", "g"), "");

	return str;
}

/**
 * Strips HTML tags from string
 *
 * @example
 * stripHTML("<b>bold</b>") // "bold"
 */
export function stripHTML(string: unknown): string {
	return String(string).replace(/(<([^>]+)>)/gi, "");
}

interface TemplateValues {
	[key: string]:
		| TemplateValues
		| TemplateValues[]
		| string
		| number
		| boolean
		| null;
}

/**
 * Replaces template placeholders in string with provided values
 *
 * @example
 * template("Hello {name}!", { name: "World" }) // "Hello World!"
 * template("{{user.email}}", { user: { email: "hi@example.com" } }) // "hi@example.com"
 * template("{missing}", {}) // "…"
 */
export function template(string: unknown, values: TemplateValues = {}): string {
	const resolve = (parts: string[], data: TemplateValues = {}) => {
		const part = escapeHTML(parts.shift() ?? "");
		const value = data[part] ?? "…";

		if (value === "…" || parts.length === 0) {
			return value;
		}

		return resolve(parts, value as TemplateValues);
	};

	const opening = "[{]{1,2}[\\s]?";
	const closing = "[\\s]?[}]{1,2}";

	const str = String(string).replace(
		new RegExp(`${opening}(.*?)${closing}`, "gi"),
		($0, $1) => String(resolve($1.split("."), values))
	);

	return str.replace(new RegExp(`${opening}.*${closing}`, "gi"), "…");
}

/**
 * Turns first letter uppercase
 *
 * @example
 * ucfirst("hello world") // "Hello world"
 */
export function ucfirst(string: unknown): string {
	const str = String(string);
	return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Turns first letter of each word uppercase
 *
 * @example
 * ucwords("hello world") // "Hello World"
 */
export function ucwords(string: unknown): string {
	return String(string)
		.split(/ /g)
		.map((word) => ucfirst(word))
		.join(" ");
}

/**
 * Turns escaped HTML entities into actual characters again
 *
 * @example
 * unescapeHTML("&lt;b&gt;bold&lt;&#x2F;b&gt;") // "<b>bold</b>"
 */
export function unescapeHTML(string: unknown): string {
	let str = String(string);

	for (const symbol in escapingMap) {
		str = str.replaceAll(escapingMap[symbol], symbol);
	}

	return str;
}

/**
 * Returns a unique ID
 *
 * @example
 * uuid() // "550e8400-e29b-41d4-a716-446655440000" (random result)
 */
export function uuid(): string {
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
