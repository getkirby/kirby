import Feature, { defaults as featureDefaults } from "./feature.js";

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

export default (panel) => {
	const parent = Feature(panel, "view", defaults());

	return {
		...parent,

		/**
		 * Opens the feature either by URL or by
		 * passing a state object
		 *
		 * @example
		 * panel.dialog.view.open({
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
			panel.dialog.close();
			panel.drawer.closeAll();
			return await parent.open.call(this, feature, options);
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
		 * Submitting view form values is not
		 * implemented yet
		 */
		/* c8 ignore next 3 */
		async submit() {
			throw new Error("Not yet implemented");
		}
	};
};
