/**
 * Resolves CSS property alias to proper CSS color values
 *
 * @param {string} string
 * @returns {string}
 */
export default function (string) {
  if (typeof string !== "string") {
    return;
  }

  // make sure case insensitive
  string = string.toLowerCase();

  if (string === "pattern") {
    return `var(--color-gray-800) var(--bg-pattern)`;
  }

  // check pre-defined color variables
  // no need to check if string starts with `#` or `var(`
  // for ex: `#000` or `var(--color-white)`
  if (string.startsWith("#") === false && string.startsWith("var(") === false) {
    const colorVariable = "--color-" + string;
    const colorComputed = window
      .getComputedStyle(document.documentElement)
      .getPropertyValue(colorVariable);

    if (colorComputed) {
      return `var(${colorVariable})`;
    }
  }

  return string;
}
