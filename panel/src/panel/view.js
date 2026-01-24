import { reactive } from "vue";
import Feature, { defaults as featureDefaults } from "./feature.js";
import { isObject } from "@/helpers/object.js";

export const defaults = () => {
	return {
		...featureDefaults(),
		breadcrumb: [],
		breadcrumbLabel: null,
		icon: null,
		id: null,
		link: null,
		search: "pages",
		title: null
	};
};

/**
 * @since 4.0.0
 */
export default (panel) => {
	const parent = Feature(panel, "view", defaults());

	return reactive({
		...parent,

		/**
		 * Load a view from the server and
		 * cancel any previous request
		 *
		 * @param {String|URL} url
		 * @param {Object|Function} options
		 * @returns {Object} Returns the current state
		 */
		async load(url, options = {}) {
			// cancel any previous request
			this.abortController?.abort();

			return parent.load.call(this, url, options);
		},

		/**
		 * Setting the active view state
		 * will also change the document title
		 * and the browser URL
		 *
		 * @param {object} state
		 * @returns {string}
		 */
		set(state) {
			// reuse the parent state setter, but with
			// the view bound as this
			parent.set.call(this, state);

			// change the document title
			panel.title = this.title;

			// get the current url
			const url = this.url().toString();

			// change the browser location and reset the scroll
			// position if the path changed
			if (window.location.toString() !== url) {
				window.history.pushState(null, null, url);
				window.scrollTo(0, 0);
			}
		},

		/**
		 * Submits view values either to a listener
		 * or to the backend
		 *
		 * @param {Object} value
		 * @param {Object} options
		 * @returns {Promise} The response object or false if the request failed
		 */
		async submit(value, options = {}) {
			if (this.isLoading === true) {
				return;
			}

			value ??= this.props.value;

			if (this.hasEventListener("submit")) {
				return this.emit("submit", value, options);
			}

			if (!this.path) {
				return false;
			}

			const response = await this.post(value, options);

			if (isObject(response) === false) {
				return response;
			}

			return this.success(response.view ?? {});
		},

		// success handlers are shared via feature helpers
	});
};
