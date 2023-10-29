import Vue from "vue";

/**
 * Checks if the dropdown element is outside of the viewport
 * horizontally and adapts the alignment if necessary
 *
 * @param {DOMRect} opener
 * @param {DOMRect} dropdown
 * @param {String} align "start"|"center"|"end"
 * @param {Number} scroll
 * @param {Number} safeSpace
 * @returns {String}
 */
export function containX(opener, dropdown, align, scroll, safeSpace = 20) {
	if (align === "end") {
		if (opener.x - scroll - dropdown.width < safeSpace) {
			// when aligning to end, cut off at the left edge
			return "start";
		}
	} else if (
		opener.x - scroll + dropdown.width + safeSpace > window.innerWidth &&
		dropdown.width + safeSpace < opener.x - scroll
	) {
		// when aligning to start, cut off at the right edge
		// (but also ensuring that it won't be cut off
		// at the left edge when aligning to end)
		return "end";
	}

	return align;
}

/**
 * Checks if the dropdown element is outside of the viewport
 * vertically and adapts the alignment if necessary
 *
 * @param {DOMRect} opener
 * @param {DOMRect} dropdown
 * @param {String} align "start"|"center"|"end"
 * @param {Number} scroll
 * @param {Number} safeSpace
 * @returns {String}
 */
export function containY(opener, dropdown, align, scroll, safeSpace = 20) {
	if (align === "top") {
		// when aligning upwards, but cut off at the top edge
		if (dropdown.height + safeSpace > opener.y - scroll) {
			return "bottom";
		}

		return "top";
	}

	// when aligning downwards, butcut off at the bottom edge
	if (opener.y - scroll + dropdown.height + safeSpace > window.innerHeight) {
		// ensure that it won't be cut off at the top edge when aligning upwards
		if (dropdown.height + safeSpace < opener.y - scroll) {
			return "top";
		}
	}

	return "bottom";
}

/**
 * Normalize alignment string to "start"|"center"|"end"
 * and flip x axis for RTL languages
 *
 * @param {String} align
 * @returns {String}
 */
export function normalizeAlignX(align) {
	if (align === "right") {
		align = "end";
	} else if (align === "left") {
		align = "start";
	}

	// flip x axis for RTL languages
	if (window.panel.direction === "rtl") {
		if (align === "start") {
			align = "end";
		} else if (align === "end") {
			align = "start";
		}
	}

	return align;
}

/**
 * Gets the x position of the dropdown element
 * (to be used with `left` CSS property on dropdown)
 *
 * @param {DOMRect} opener
 * @param {DOMRect} dropdown
 * @param {String} align "start"|"center"|"end"
 * @returns {Number}
 */
export function positionX(opener, dropdown, align) {
	if (align === "center") {
		return opener.x + opener.width / 2 - dropdown.width / 2;
	}

	if (align === "end") {
		return opener.x + opener.width - dropdown.width;
	}

	return opener.x;
}

/**
 * Gets the y position of the dropdown element
 * (to be used with `top` CSS property on dropdown)
 *
 * @param {DOMRect} opener
 * @param {DOMRect} dropdown
 * @param {String} align "start"|"center"|"end"
 * @returns {Number}
 */
export function positionY(opener, dropdown, align) {
	if (align === "top") {
		return opener.y - dropdown.height;
	}

	return opener.y + opener.height;
}

/**
 * Returns x and y position of the dropdown element
 * (to be used with `left` and `top` CSS properties on fixed element)
 *
 * @param {Element} opener
 * @param {Element} dropdown
 * @param {String} x "start"|"center"|"end"
 * @param {String} y "top"|"bottom"
 * @param {Object} scroll
 * @returns {Object}
 */
export function position(opener, dropdown, x, y, scroll) {
	// drill down to the element of a component
	if (opener instanceof Vue) {
		opener = opener.$el;
	}

	// get the dimensions of the opening button and dropdown element
	opener = opener.getBoundingClientRect();
	dropdown = dropdown.getBoundingClientRect();

	// adapt aligment to contain dropdown element in viewport
	x = normalizeAlignX(x);
	x = containX(opener, dropdown, x, scroll.x);
	y = containY(opener, dropdown, y, scroll.y);

	return {
		x: positionX(opener, dropdown, x),
		y: positionY(opener, dropdown, y)
	};
}
