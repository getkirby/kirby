/**
 * Helper to set the focus inside an
 * HTML element
 * @param {HTMLElement} element
 * @returns {HTMLElement|false}
 */
export default function focus(element) {
	let target = element?.querySelector(`
		[autofocus],
		[data-autofocus],
		input,
		textarea,
		select,
		button
	`);

	if (typeof target?.focus === "function") {
		target.focus();
		return target;
	}

	return false;
}
