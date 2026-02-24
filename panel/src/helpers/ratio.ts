/**
 * Returns a percentage string for the provided fraction
 *
 * @param fraction - fraction to convert to a percentage
 * @param fallback - default value if fraction cannot be parsed
 * @param vertical - Whether the fraction is applied to
 *                   vertical or horizontal orientation
 */
export default function (
	fraction: string = "3/2",
	fallback: string = "100%",
	vertical: boolean = true
): string {
	const parts = String(fraction).split("/");

	if (parts.length !== 2) {
		return fallback;
	}

	const a = Number(parts[0]);
	const b = Number(parts[1]);

	if (a === 0 || b === 0) {
		return "100%";
	}

	const result = vertical ? (100 / a) * b : (100 / b) * a;
	return parseFloat(result.toFixed(2)) + "%";
}
