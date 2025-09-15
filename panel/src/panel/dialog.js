import { reactive } from "vue";
import Modal, { defaults as modalDefaults } from "./modal.js";

export const defaults = () => {
	return {
		...modalDefaults(),
		// when drawers or dialogs are created with the
		// deprecated way of adding a dialog/drawer component
		// to a template, `legacy` is set to true in the open method
		// and the matching modal component will not load it.
		legacy: false,
		// Store for the Vue component reference
		// This will make it possible to hackishly
		// support directly rendered components
		ref: null
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
		 * Closes the modal
		 */
		async close() {
			// close legacy components
			// if it is still open
			if (this.ref) {
				this.ref.visible = false;
			}

			parent.close.call(this);
		},

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
			// check for legacy Vue components
			if (dialog instanceof window.Vue) {
				return this.openComponent(dialog);
			}

			// prefix URLs
			if (typeof dialog === "string") {
				dialog = `/dialogs/${dialog}`;
			}

			const state = await parent.open.call(this, dialog, options);

			// add it to the history
			this.history.add(state, dialog.replace);

			return state;
		},

		/**
		 * Takes a legacy dialog component and
		 * opens it manually.
		 *
		 * @param {any} dialog Vue component
		 * @deprecated 4.0.0
		 */
		async openComponent(dialog) {
			panel.deprecated(
				"Dialog components should no longer be used in your templates"
			);

			const state = await parent.open.call(this, {
				component: dialog.$options._componentTag,
				// don't render this in the modal
				// component. The Vue component already
				// takes over rendering.
				legacy: true,
				// Use a combination of attributes and props
				// to get everything that was passed to the component
				props: {
					...dialog.$attrs,
					...dialog.$props
				},
				// add a reference to the Vue component
				// This will make it possible to determine
				// its open state in the dialog or drawer components
				ref: dialog
			});

			const listeners = this.listeners();

			for (const listener in listeners) {
				dialog.$on(listener, listeners[listener]);
			}

			dialog.visible = true;

			return state;
		}
	});
};
