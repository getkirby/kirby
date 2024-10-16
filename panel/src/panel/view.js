import { reactive } from "vue";
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

/**
 * @since 4.0.0
 */
export default (panel) => {
	const parent = Feature(panel, "view", defaults());

	return reactive({
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
	});
};
