import Vue from "vue";
import { hasProperty, isObject } from "@/helpers/object.js";
import Feature, { defaults as featureDefaults } from "./feature.js";

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
				this.close();
			}
		},

		/**
		 * Closes the island
		 */
		async close() {
			if (this.isOpen) {
				this.emit("close");
				this.reset();
			}
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
		 * @param {Object} state
		 * @returns {Object} The new state
		 */
		async open(feature, options = {}) {
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
		 * @returns {Object} The new state or false if the request failed
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
			return this.submitSuccessHandler(response[this.key()] ?? {});
		},

		/**
		 * Dispatch deprecated store events
		 *
		 * @param {Object} state
		 * @param {String|Array} events
		 */
		submitSuccessDispatch(state) {
			if (isObject(state.dispatch) === false) {
				return;
			}

			Object.keys(state.dispatch).forEach((event) => {
				const payload = state.dispatch[event];
				panel.vue?.$store.dispatch(
					event,
					Array.isArray(payload) === true ? [...payload] : payload
				);
			});
		},

		/**
		 * Emit all events that might be in the response
		 *
		 * @param {Object} state
		 * @param {String|Array} events
		 */
		submitSuccessEvents(state) {
			if (state.event) {
				// wrap single events to treat them all at once
				const events =
					Array.isArray(state.event) === true ? state.event : [state.event];

				// emit all defined events
				events.forEach((event) => {
					if (typeof event === "string") {
						panel.events.emit(event, state);
					}
				});
			}

			// emit a success event
			if (hasProperty(state, "emit") === false || state.emit !== false) {
				panel.events.emit("success", state);
			}
		},

		/**
		 * This is rebuilding the previous
		 * behaviours from the dialog mixin.
		 * Most of the response handling should
		 * be redone. But we keep it for compatibility
		 *
		 * @param {Object} state
		 * @returns
		 */
		submitSuccessHandler(state) {
			// close the dialog or drawer
			this.close();

			// show a success message
			this.submitSuccessNotification(state);

			// send custom events to the event bus
			this.submitSuccessEvents(state);

			// dispatch store actions that might have been defined in the response
			this.submitSuccessDispatch(state);

			// handle any redirects
			this.submitSuccessRedirect(state);

			// reload the parent view to show changes
			panel.view.reload(state.reload);

			return state;
		},

		/**
		 * Sends a success notification if the
		 * response contains a success message
		 *
		 * @param {Object} state
		 */
		submitSuccessNotification(state) {
			if (state.message) {
				panel.notification.success(state.message);
			}
		},

		/**
		 * Handles redirects in submit responses
		 *
		 * @param {Object} state
		 */
		submitSuccessRedirect(state) {
			// @deprecated Use state.redirect instead
			if (state.route) {
				return panel.view.open(state.route);
			}

			if (state.redirect) {
				return panel.view.open(state.redirect);
			}
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
