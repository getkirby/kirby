/**
 * Helper to set the focus inside an HTML element
 * @since 4.0.0
 *
 * @example
 * const form = document.querySelector(".k-form");
 * focus(form) // focuses first input in form
 * focus(".k-dialog", "title") // focuses field named "title" inside dialog
 */
export default function focus(
	element: HTMLElement | string | null,
	field?: string
): HTMLElement | false {
	if (typeof element === "string") {
		element = document.querySelector<HTMLElement>(element);
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
 */
export function focusIsInModal(element: HTMLElement): boolean {
	return (
		element.closest(".k-dialog") !== null ||
		element.closest(".k-drawer") !== null
	);
}

/**
 * Tries to find a focusable child
 *
 * @example
 * focusTarget(form, ["[autofocus]", "input"])
 */
export function focusTarget(
	parent: HTMLElement,
	selectors: string[]
): HTMLElement | null {
	for (const selector of selectors) {
		const element = parent.querySelector<HTMLElement>(selector);

		if (isFocusable(element) === true) {
			return element;
		}
	}

	return null;
}

/**
 * Checks if the given HTML Element is focusable
 */
export function isFocusable(element: HTMLElement | null): boolean {
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
