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

			// change the browser location if the path changed
			if (window.location.toString() !== url) {
				window.history.pushState(null, null, url);
			}
		}
	};
};
