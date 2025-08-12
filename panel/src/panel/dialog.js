import { reactive } from "vue";
import Modal, { defaults as modalDefaults } from "./modal.js";

export const defaults = () => {
	return {
		...modalDefaults()
	};
};

/**
 * @since 4.0.0
 */
export default (panel) => {
	// shortcut to submit dialogs
	panel.events.on("dialog.save", (e) => {
		e?.preventDefault?.();
		panel.dialog.submit();
	});

	const parent = Modal(panel, "dialog", defaults());

	return reactive({
		...parent,

		/**
		 * Opens dialog via JS object or loads it from the server
		 *
		 * @example
		 * panel.dialog.open('some/dialog');
		 *
		 * @example
		 * panel.dialog.open('some/dialog', () => {
		 *  // on submit
		 * });
		 *
		 * @example
		 * panel.dialog.open('some/dialog', {
		 *   query: {
		 *     template: 'some-template'
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 *
		 * @example
		 * panel.dialog.open({
		 *   component: 'k-remove-dialog',
		 *   props: {
		 *      text: 'Do you really want to delete this?'
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 *
		 * @param {String|Object} dialog
		 * @param {Object|Function} options
		 * @returns {Object}
		 */
		async open(dialog, options = {}) {
			// prefix URLs
			if (typeof dialog === "string") {
				dialog = `/dialogs/${dialog}`;
			}

			const state = await parent.open.call(this, dialog, options);

			// add it to the history
			this.history.add(state, dialog.replace);

			return state;
		}
	});
};
