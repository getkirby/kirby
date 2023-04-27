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
		timestamp: null,
		title: null
	};
};

export default (panel) => {
	const parent = Feature(panel, "view", defaults());

	return {
		...parent,

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

			// change the browser location if the path changed
			if (window.location.toString() !== this.url().toString()) {
				window.history.pushState(null, null, this.path);
			}
		},

		/**
		 * Submitting view form values is not
		 * implemented yet
		 */
		async submit() {
			throw new Error("Not yet implemented");
		}
	};
};
