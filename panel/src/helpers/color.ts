/**
 * Resolves CSS alias to proper color value
 */
export default function (string: string): string {
	if (typeof string !== "string") {
		return;
	}

	// make sure case insensitive
	string = string.toLowerCase();

	if (string === "pattern") {
		return `var(--color-gray-800) var(--bg-pattern)`;
	}

	// check if pre-defined color variables exists and can be used;
	// no need to check if string starts with `#` or `var(`
	// for ex: `#000` or `var(--color-white)`
	if (string.startsWith("#") === false && string.startsWith("var(") === false) {
		const variable = "--color-" + string;
		const computed = window
			.getComputedStyle(document.documentElement)
			.getPropertyValue(variable);

		if (computed) {
			return `var(${variable})`;
		}
	}

	return string;
}
