/**
 * Resolves CSS property alias to proper CSS color values
 *
 * @example
 * color("red") // => "var(--color-red)"
 * color("#ff0000") // => "#ff0000"
 * color("pattern") // => "var(--pattern)"
 */
export default function (input: unknown): string | undefined {
	if (typeof input !== "string") {
		return;
	}

	// make the string case-insensitive
	const string = input.toLowerCase();

	if (string === "pattern") {
		return `var(--pattern)`;
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
