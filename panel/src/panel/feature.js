import { reactive } from "vue";
import { isUrl } from "@/helpers/url";
import listeners from "./listeners.js";
import State from "./state.js";

/**
 * Default state for all features
 */
export const defaults = () => {
	return {
		// request abort controller
		abortController: null,
		// the feature component
		component: null,
		// loading state
		isLoading: false,
		// event listeners
		on: {},
		// relative path to this feature
		path: null,
		// all props for the feature component
		props: {},
		// the query parameters form the latest request
		query: {},
		// referrer can be used to redirect properly in handlers
		referrer: null,
		// timestamp from the backend to force refresh the reactive state
		timestamp: null
	};
};

/**
 * Feature objects isolate functionality and state
 * of Panel features like drawers, dialogs,
 * notifications and views.
 * @since 4.0.0
 *
 * @param {Object} panel The panel singleton
 * @param {String} key Sets the key for the feature. Backend responses use this key for features.
 * @param {Object} defaults Sets the default state of the feature
 */
export default (panel, key, defaults) => {
	const parent = State(key, defaults);

	return reactive({
		/**
		 * Features inherit all the state methods
		 * and reactive defaults are also merged
		 * through them.
		 */
		...parent,
		...listeners(),

		/**
		 * Sends a get request to the backend route for
		 * this Feature
		 * @since 5.1.0
		 *
		 * @param {Object} value
		 * @param {Object} options
		 */
		async get(url, options = {}) {
			this.isLoading = true;

			try {
				return await panel.get(url, options);
			} catch (error) {
				panel.error(error);
			} finally {
				this.isLoading = false;
			}

			return false;
		},

		/**
		 * Loads a feature from the server
		 * and opens it afterwards
		 *
		 * @example
		 * panel.view.load("/some/view");
		 *
		 * @example
		 * panel.view.load("/some/view", () => {
		 *   // submit
		 * });
		 *
		 * @example
		 * panel.view.load("/some/view", {
		 *   query: {
		 *     search: "Find me"
		 *   }
		 * });
		 *
		 * @param {String|URL} url
		 * @param {Object|Function} options
		 * @returns {Object} Returns the current state
		 */
		async load(url, options = {}) {
			// each feature can have its own loading state
			// the panel.open method also triggers the global loading
			// state for the entire panel. This adds fine-grained controll
			// over apropriate spinners.
			if (options.silent !== true) {
				this.isLoading = true;
			}

			// create a new abort controller
			// and add to the options
			this.abortController = new AbortController();
			options.signal = this.abortController.signal;

			// the global open method is used to make sure
			// that a response can also trigger other features.
			// For example, a dialog request could also open a drawer
			// or a notification by sending the matching object
			await panel.open(url, options);

			// stop the feature loader
			this.isLoading = false;

			// add additional listeners from the options
			this.addEventListeners(options.on);

			// return the final state
			return this.state();
		},

		/**
		 * Opens the feature either by URL or by
		 * passing a state object
		 *
		 * @example
		 * panel.dialog.view({
		 *   component: "k-page-view",
		 *	 props: {},
		 *   on: {
		 *     submit: () => {}
		 * 	 }
		 * });
		 *
		 * See load for more examples
		 *
		 * @param {String|URL|Object} feature
		 * @param {Object|Function} options
		 * @returns {Object} Returns the current state
		 */
		async open(feature, options = {}) {
			// simple wrapper to allow passing a submit handler
			// as second argument instead of the options
			if (typeof options === "function") {
				options = {
					on: {
						submit: options
					}
				};
			}

			// the feature needs to be loaded first
			// before it can be opened. This will route
			// the request through panel.open
			if (isUrl(feature) === true) {
				return this.load(feature, options);
			}

			// set the new state
			this.set(feature);

			// add additional listeners from the options
			this.addEventListeners(options.on);

			// trigger optional open listeners
			this.emit("open", feature, options);

			// return the final state
			return this.state();
		},

		/**
		 * Sends a post request to the backend route for
		 * this Feature
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
			value ??= this.props?.value ?? {};

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
		 * Reloads the properties for the feature
		 */
		async refresh(options = {}) {
			options.url ??= this.url();

			const response = await this.get(options.url, options);
			const state = response[this.key()];

			// the state cannot be updated
			if (!state || state.component !== this.component) {
				return;
			}

			this.props = state.props;

			return this.state();
		},

		/**
		 * If the feature has a path, it can be reloaded
		 * with this method to replace/refresh its state
		 *
		 * @example
		 * panel.view.reload();
		 *
		 * @param {Object, Boolean} options
		 */
		async reload(options = {}) {
			if (!this.path) {
				return false;
			}

			this.open(this.url(), options);
		},

		/**
		 * Sets a new active state for the feature
		 * This is done whenever the state is an object
		 * and not undefined or null
		 *
		 * @param {Object} state
		 */
		set(state) {
			parent.set.call(this, state);

			// reset the event listeners
			this.on = {};

			// register new listeners
			this.addEventListeners(state.on ?? {});

			return this.state();
		},

		/**
		 * Creates a full URL object for the current path
		 *
		 * @returns {URL}
		 */
		url() {
			return panel.url(this.path, this.query);
		}
	});
};
