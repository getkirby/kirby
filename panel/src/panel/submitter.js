import { isObject } from "@/helpers/object.js";

export default (panel) => {
	return {
		/**
		 * Checks if the feature can be submitted
		 *
		 * @returns {Boolean}
		 */
		hasSubmitter() {
			// the feature has a custom submit listener
			if (this.hasEventListener("submit") === true) {
				return true;
			}

			// the feature can be submitted to the backend
			if (typeof this.path === "string") {
				return true;
			}

			return false;
		},

		/**
		 * Sends a post request to the backend route for
		 * this feature
		 *
		 * @param {Object} value
		 * @param {Object} options
		 */
		async post(value, options = {}) {
			if (!this.path) {
				throw new Error(`The ${this.key()} cannot be posted`);
			}

			// start the loader
			this.isLoading = true;

			// if no value has been passed to the submit method,
			// take the value object from the props
			value = value ?? this.props?.value ?? {};

			try {
				return await panel.post(this.path, value, options);
			} catch (error) {
				panel.error(error);
			} finally {
				// stop the loader
				this.isLoading = false;
			}

			return false;
		},

		/**
		 * Custom submitter for the dialog/drawer
		 * It will automatically close the modal
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
				response = await this.emit("submit", value ?? this.value, options);
			} else {
				// send a request to the backend
				response = await this.post(value ?? this.value, options);
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

			// close the feature if it can be closed
			if (typeof this.close === "function") {
				this.close();
			}

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
		 * Drawers, dialogs and views might likely have forms
		 * so this seems useful.
		 *
		 * @returns {Object}
		 */
		get value() {
			return this.props?.value;
		}
	};
};
