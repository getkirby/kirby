/**
 * Formats a number in the interface language
 * @since 5.6.2
 *
 * @example
 * format(1234.5) // => "1,234.5" (en) or "1.234,5" (de)
 * format(1.5, { minimumFractionDigits: 2 }) // => "1.50" (en)
 */
export function format(
	value: number,
	options: Intl.NumberFormatOptions = {}
): string {
	// `panel.translation` sets it as a valid language tag
	const locale = document.documentElement.lang || "en";
	return new Intl.NumberFormat(locale, options).format(value);
}

export default {
	format
};
