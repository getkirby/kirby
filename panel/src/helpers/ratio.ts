/**
 * Returns a percentage string for the provided fraction
 */
export default (
	/** Fraction to convert to a percentage */
	fraction = "3/2",
	/** Default value if fraction cannot be parsed */
	fallback = "100%",
	/** Whether the fraction is applied to vertical or horizontal orientation */
	vertical = true
): string => {
	const parts = String(fraction).split("/");

	if (parts.length !== 2) {
		return fallback;
	}

	const a = Number(parts[0]);
	const b = Number(parts[1]);
	let result = 100;

	if (a !== 0 && b !== 0) {
		if (vertical) {
			result = (result / a) * b;
		} else {
			result = (result / b) * a;
		}

		result = parseFloat(String(result));
	}

	return result.toFixed(2) + "%";
};
