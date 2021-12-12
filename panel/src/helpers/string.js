import "./regex.js";

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
  const entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
    "/": "&#x2F;",
    "`": "&#x60;",
    "=": "&#x3D;"
  };

  return String(string).replace(/[&<>"'`=/]/g, (char) => entityMap[char]);
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

  // Source: https://thekevinscott.com/emojis-in-javascript/
  const result = string.match(
    // eslint-disable-next-line no-misleading-character-class
    /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|[\ud83c\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|[\ud83c\ude32-\ude3a]|[\ud83c\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/i
  );

  return result !== null && result.length !== null;
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
 * Convert string to ASCII slug
 * @param {string} string string to be converted
 * @param {array} rules ruleset to convert non-ASCII characters
 * @param {array} allowed list of allowed non-ASCII characters
 * @param {string} separator character used to replace e.g. spaces
 * @returns {string}
 */
export function slug(string, rules = [], allowed = "", separator = "-") {
  if (!string) {
    return "";
  }

  allowed = "a-z0-9" + allowed;
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

  // replace spaces with simple dashes
  string = string.replace(new RegExp("[^" + allowed + "]", "ig"), separator);

  // remove double separators
  string = string.replace(
    new RegExp("[" + RegExp.escape(separator) + "]{2,}", "g"),
    separator
  );

  // replace slashes with dashes
  string = string.replace("/", separator);

  // trim leading and trailing non-word-chars
  string = string.replace(new RegExp("^[^" + allowed + "]+", "g"), "");
  string = string.replace(new RegExp("[^" + allowed + "]+$", "g"), "");

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
 * Replaces template placeholders in string
 * with provided values
 * @param {string} string
 * @param {Object} values
 * @returns {string}
 */
export function template(string, values = {}) {
  const resolve = (parts, data = {}) => {
    const part = escapeHTML(parts.shift());
    const value = data[part] ?? null;

    if (value === null) {
      return Object.prototype.hasOwnProperty.call(data, part) || "…";
    }
    if (parts.length === 0) {
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
  lcfirst,
  pad,
  random,
  slug,
  stripHTML,
  template,
  ucfirst,
  ucwords,
  uuid
};
