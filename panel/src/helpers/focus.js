/**
 * Helper to set the focus inside an
 * HTML element
 * @param {HTMLElement} element
 * @returns {HTMLElement|false}
 */
export default function focus(element) {
	if (!element) {
		return false;
	}

	// call the focus method of the element if it has one
	if (typeof element.focus === "function") {
		element.focus();
		return element;
	}

	// search for the first focusable element inside this element
	let target = element?.querySelector(`
		[autofocus],
		[data-autofocus],
		input,
		textarea,
		select,
		button
	`);

	// check if that element has a focus method
	if (typeof target?.focus === "function") {
		target.focus();
		return target;
	}

	// return false if nothing could be focused
	return false;
}
