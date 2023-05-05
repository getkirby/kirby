// @ts-check

import { isObject } from "@/helpers/object.js";
import Feature, { defaults as featureDefaults } from "./feature.js";
import focus from "@/helpers/focus.js";

/**
 * Additional default values for islands
 */
export const defaults = () => {
	return {
		...featureDefaults(),
		// when drawers or dialogs are created with the
		// deprecated way of adding a dialog/drawer component
		// to a template, `island` is set to false in the open method
		// and the matching island component will not load it.
		island: true,
		// open state for the island
		isOpen: false,
		// Store for the Vue component reference
		// This will make it possible to determine
		// its open state in the dialog or drawer components
		// It would not be needed if we load all dialogs
		// and drawers through islands
		ref: null
	};
};

/**
 * An island is a feature that can be opened and
 * closed and will be placed in the Panel by the matching
 * Island component
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
		 * Closes the island
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
		 * or button in the island. The input can also
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
		 * before an island is opened. It also sets the
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
				// don't render this in the island
				// comonent. The Vue component already
				// takes over rendering.
				island: false,
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
			// contexts, if the island wasn't open so far
			if (this.isOpen === false) {
				panel.notification.close();
			}

			// open the feature via url or with a state object
			await parent.open.call(this, feature, options);

			// mark the island as open
			this.isOpen = true;

			return this.state();
		},

		/**
		 * Custom submitter for the dialog/drawer
		 * It will automatically close the island
		 * if there's no submit listner or backend route.
		 *
		 * @param {Object} value
		 * @param {Object} options
		 * @returns {Promise} The new state or false if the request failed
		 */
		async submit(value, options = {}) {
			// close the drawer or dialog if there's no submitter
			// An example for this is the blocks drawer
			if (this.hasSubmitter() === false) {
				console.warn(`There's no ${this.key()} submitter`);
				return this.close();
			}

			let response;

			if (this.hasEventListener("submit")) {
				// call a custom submit handler if it exists
				response = await this.emit("submit", value, options);
			} else {
				// send a request to the backend
				response = await this.post(value, options);
			}

			// the request failed and should have raised an error
			if (isObject(response) === false) {
				return response;
			}

			// get details from the response object.
			// I.e. { $dialog: { ... } }
			// pass it forward to the success handler
			// to react on elements in the response
			return this.success(response["$" + this.key()] ?? {});
		},

		/**
		 * This is rebuilding the previous
		 * behaviours from the dialog mixin.
		 * Most of the response handling should
		 * be redone. But we keep it for compatibility
		 *
		 * @param {Object|String} success
		 * @returns
		 */
		success(success) {
			if (typeof success === "string") {
				panel.notification.success(success);
			}

			// close the island
			this.close();

			// show a success message
			this.successNotification(success);

			// send custom events to the event bus
			this.successEvents(success);

			// dispatch store actions that might have been defined in the response
			this.successDispatch(success);

			// redirect or reload
			if (success.route || success.redirect) {
				// handle any redirects
				this.successRedirect(success);
			} else {
				// reload the parent view to show changes
				panel.view.reload(success.reload);
			}

			return success;
		},

		/**
		 * Dispatch deprecated store events
		 *
		 * @param {Object} state
		 */
		successDispatch(state) {
			if (isObject(state.dispatch) === false) {
				return;
			}

			for (const event in state.dispatch) {
				const payload = state.dispatch[event];
				panel.app.$store.dispatch(
					event,
					Array.isArray(payload) === true ? [...payload] : payload
				);
			}
		},

		/**
		 * Emit all events that might be in the response
		 *
		 * @param {Object} state
		 */
		successEvents(state) {
			if (state.event) {
				// wrap single events to treat them all at once
				const events = Array.wrap(state.event);

				// emit all defined events
				for (const event of events) {
					if (typeof event === "string") {
						panel.events.emit(event, state);
					}
				}
			}

			// emit a success event
			if (state.emit !== false) {
				panel.events.emit("success", state);
			}
		},

		/**
		 * Sends a success notification if the
		 * response contains a success message
		 *
		 * @param {Object} state
		 */
		successNotification(state) {
			if (state.message) {
				panel.notification.success(state.message);
			}
		},

		/**
		 * Handles redirects in submit responses
		 *
		 * @param {Object} state
		 */
		successRedirect(state) {
			const redirect = state.route ?? state.redirect;

			// no redirect
			if (!redirect) {
				return false;
			}

			if (typeof redirect === "string") {
				return panel.open(redirect);
			}

			return panel.open(redirect.url, redirect.options);
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
