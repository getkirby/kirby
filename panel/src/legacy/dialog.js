/**
 * Defines dialogs via JS object or loads it from the server
 *
 * @example
 * this.$dialog('some/dialog');
 *
 * @example
 * this.$dialog('some/dialog', () => {
 *  // on submit
 * });
 *
 * @example
 * this.$dialog('some/dialog', {
 *   query: {
 *     template: 'some-template'
 *   },
 *   submit: () => {},
 *   cancel: () => {}
 * });
 *
 * @example
 * this.$dialog({
 *   component: 'k-remove-dialog',
 *   props: {
 *      text: 'Do you really want to delete this?'
 *   },
 *   submit: () => {},
 *   cancel: () => {}
 * });
 *
 * @deprecated since 4.0 Use panel.dialog.open instead
 * @param {String|Object} dialog
 * @param {Object|Function} optio
 */
export default async (dialog, options = {}) => {
	// deprecated prefixing. Dialog routes should not be treated differently in the future
	if (typeof dialog === "string") {
		dialog = `/dialogs/${dialog}`;
	}

	return window.panel.dialog.open(dialog, options);
};
