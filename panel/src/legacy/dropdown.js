/**
 * Loads dropdown options from the server
 *
 * @example
 * <k-dropdown-content :options="$dropdown('some/dropdown')" />
 *
 * @deprecated since 4.0 Use panel.dropdown.open instead
 * @param {String|Object} path
 * @param {Object} options
 * @returns {Function} Returns an asynchronous function to be lazy load options
 */
export default (dropdown, options = {}) => {
	// deprecated prefixing. Dropdown routes should not be treated differently in the future
	if (typeof dropdown === "string") {
		dropdown = `/dropdowns/${dropdown}`;
	}

	// deprecated lazy loading of dropdown routes
	return async (ready) => {
		await window.panel.dropdown.open(dropdown, options);

		// load all options from the dropdown
		const items = window.panel.dropdown.options();

		// react to empty dropdowns
		if (items.length === 0) {
			throw Error(`The dropdown is empty`);
		}

		ready(items);
	};
};
