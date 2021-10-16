export function camelToKebab(string) {
  return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
}

/**
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

  return String(string).replace(/[&<>"'`=/]/g, (char) => {
    return entityMap[char];
  });
}

export function hasEmoji(string) {
  if (typeof string !== "string") {
    return false;
  }

  // Source: https://thekevinscott.com/emojis-in-javascript/
  // eslint-disable-next-line no-misleading-character-class
  const result = string.match(
    /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|[\ud83c\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|[\ud83c\ude32-\ude3a]|[\ud83c\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/i
  );

  return result !== null && result.length !== null;
}

export function lcfirst(string) {
  const str = String(string);
  return str.charAt(0).toLowerCase() + str.substr(1);
}

export function random(length) {
  let result = "";
  const characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  const charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

export function stripHTML(string) {
  const str = String(string);
  return str.replace(/(<([^>]+)>)/gi, "");
}

export function template(string, values = {}) {
  const resolve = function (parts, data = {}) {
    const part = escapeHTML(parts.shift());
    const value = data[part] ?? null;

    if (value === null) {
      return Object.prototype.hasOwnProperty.call(data, part) || "…";
    } else if (parts.length === 0) {
      return value;
    } else {
      return resolve(parts, value);
    }
  };

  const opening = "[{]{1,2}[\\s]?";
  const closing = "[\\s]?[}]{1,2}";

  string = string.replace(
    new RegExp(`${opening}(.*?)${closing}`, "gi"),
    ($0, $1) => {
      return resolve($1.split("."), values);
    }
  );

  return string.replace(new RegExp(`${opening}.*${closing}`, "gi"), "…");
}

export function ucfirst(string) {
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.substr(1);
}

export function ucwords(string) {
  const str = String(string);
  return str
    .split(/ /g)
    .map((word) => ucfirst(word))
    .join(" ");
}

export default {
  camelToKebab,
  escapeHTML,
  hasEmoji,
  lcfirst,
  random,
  stripHTML,
  template,
  ucfirst,
  ucwords
};
