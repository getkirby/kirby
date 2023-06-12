// @ts-check

import Feature, { defaults as featureDefaults } from "./feature.js";
import focus from "@/helpers/focus.js";
import "@/helpers/array.js";

/**
 * Additional default values for modals
 */
export const defaults = () => {
	return {
		...featureDefaults(),
		// open state for the modal
		isOpen: false,
		// when drawers or dialogs are created with the
		// deprecated way of adding a dialog/drawer component
		// to a template, `legacy` is set to true in the open method
		// and the matching modal component will not load it.
		legacy: false,
		// Store for the Vue component reference
		// This will make it possible to determine
		// its open state in the dialog or drawer components
		// It would not be needed if we load all dialogs
		// and drawers through modals
		ref: null
	};
};

/**
 * A modal is a feature that can be opened and
 * closed and will be placed in the Panel by the matching
 * Modal component
 *
 * @param {any} panel
 * @param {string} key
 * @param {object} defaults
 */
export default (panel, key, defaults) => {
	const parent = Feature(panel, key, defaults);

	return {
		...parent,

		/**
		 * Dialogs and drawers can be cancelled. This could
		 * later be connected with a backend endpoint to
		 * run an action based on cancellation. Maybe delete an
		 * entry or something.
		 */
		async cancel() {
			if (this.isOpen) {
				this.emit("cancel");
			}

			this.close();
		},

		/**
		 * Closes the modal
		 */
		async close() {
			// close legacy components
			// if it is still open
			this.ref?.hide();

			if (this.isOpen) {
				this.emit("close");
			}

			this.reset();
		},

		/**
		 * Set the focus to the first focusable input
		 * or button in the modal. The input can also
		 * be set manually.
		 *
		 * @param {String} input
		 */
		focus(input) {
			focus(`.k-${this.key()}-portal`, input);
		},

		/**
		 * Form drawers and dialogs can use this
		 * to update their value property and also
		 * fire an input event.
		 *
		 * @param {Object} value
		 */
		input(value) {
			if (this.isOpen === false) {
				return;
			}

			this.props.value = value;
			this.emit("input", value);
		},

		/**
		 * Customized setter for the active state, which
		 * will make sure to close unwanted notifications
		 * before a modal is opened. It also sets the
		 * isOpen state.
		 *
		 * @param {Object} feature
		 * @returns {Promise} Returns the new state
		 */
		async open(feature, options = {}) {
			// check for legacy Vue components
			if (feature instanceof window.Vue) {
				return this.openComponent(feature);
			}

			return this.openState(feature, options);
		},

		/**
		 * Takes a legacy Vue component and
		 * opens it manually.
		 *
		 * @param {any} component
		 */
		async openComponent(component) {
			const state = await this.openState({
				component: component.$options._componentTag,
				// don't render this in the modal
				// component. The Vue component already
				// takes over rendering.
				legacy: true,
				// Use a combination of attributes and props
				// to get everything that was passed to the component
				props: {
					...component.$attrs,
					...component.$props
				},
				// add all registered listeners on the component
				on: component.$listeners,
				// add a reference to the Vue component
				// This will make it possible to determine
				// its open state in the dialog or drawer components
				ref: component
			});

			component.show();

			return state;
		},

		/**
		 * Opens the state by object or URL
		 * @param {String|Object|URL} feature
		 * @param {Object} options
		 * @returns {Promise}
		 */
		async openState(feature, options) {
			// close previous notifications from other
			// contexts, if the modal wasn't open so far
			if (this.isOpen === false) {
				panel.notification.close();
			}

			// open the feature via url or with a state object
			await parent.open.call(this, feature, options);

			// mark the modal as open
			this.isOpen = true;

			return this.state();
		},

		/**
		 * Quick access to the value prop.
		 * Drawers and dialogs might likely have forms
		 * so this seems useful.
		 *
		 * @returns {Object}
		 */
		get value() {
			return this.props?.value;
		}
	};
};
