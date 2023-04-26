/**
 * Helper to set the focus inside an
 * HTML element
 * @param {String|HTMLElement} element
 * @param {String} field
 * @returns {HTMLElement|false}
 */
export default function focus(element, field) {
	if (typeof element === "string") {
		element = document.querySelector(element);
	}

	if (!element) {
		return false;
	}

	const selectors = [
		"[autofocus]",
		"[data-autofocus]",
		"input",
		"textarea",
		"select",
		"button"
	];

	// add a selector for a specific field
	// to the beginning of the selector array
	if (field) {
		selectors.unshift(`[name="${field}"]`);
	}

	// try to find a focusable element
	const target = focusTarget(element, selectors);

	// check if a focusable child was found
	if (target) {
		target.focus();
		return target;
	}

	// call the focus method of the element if it has one
	if (isFocusable(element) === true) {
		element.focus();
		return element;
	}

	// return false if nothing could be focused
	return false;
}

/**
 * Tries to find a focusable child
 * @param {HTMLElement} parent
 * @param {Array} selectors
 * @returns {HTMLElement|null}
 */
export function focusTarget(parent, selectors) {
	for (const selector of selectors) {
		const element = parent.querySelector(selector);

		if (isFocusable(element) === true) {
			return element;
		}
	}

	return null;
}

/**
 * Checks if the given HTML Element is
 * focusable.
 * @param {HTMLElement|null} element
 * @returns {Boolean}
 */
export function isFocusable(element) {
	if (!element) {
		return false;
	}

	if (element.matches("[disabled], [aria-disabled], input[type=hidden]")) {
		return false;
	}

	if (typeof element.focus === "function") {
		return true;
	}

	return false;
}
