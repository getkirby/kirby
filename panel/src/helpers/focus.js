/**
 * Helper to set the focus inside an HTML element
 * @since 4.0.0
 *
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

	// prevent setting focus if an item inside element (e.g. the dialog)
	// already holds the focus currently
	if (
		!field &&
		element.contains(document.activeElement) &&
		element !== document.activeElement
	) {
		return false;
	}

	const selectors = [
		// prioritize elements that have set autofocus explicitly
		":where([autofocus], [data-autofocus])",
		// treat all types of inputs equally as second-best
		":where(input, textarea, select, [contenteditable=true], .input-focus)",
		// prefer submit button over other buttons
		"[type=submit]",
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
 * Checks if the focused element is in a drawer or dialog
 *
 * @param {HTMLElement} element
 */
export function focusIsInModal(element) {
	return element.closest?.(".k-dialog") || element.closest?.(".k-drawer");
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

	// check if the element itself is disabled
	if (element.matches("[disabled], [aria-disabled], input[type=hidden]")) {
		return false;
	}

	// check if the element is a child of a disabled element
	if (element.closest("[aria-disabled]") || element.closest("[disabled]")) {
		return false;
	}

	if (typeof element.focus === "function") {
		return true;
	}

	return false;
}
