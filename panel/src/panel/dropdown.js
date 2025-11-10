import Feature, { defaults } from "./feature.js";
import { reactive } from "vue";

/**
 * @since 4.0.0
 */
export default (panel) => {
	const parent = Feature(panel, "dropdown", defaults());

	return reactive({
		...parent,

		close() {
			this.emit("close");
			this.reset();
		},

		open(dropdown, options = {}) {
			// prefix URLs
			if (typeof dropdown === "string") {
				dropdown = `/dropdowns/${dropdown}`;
			}

			return parent.open.call(this, dropdown, options);
		},

		/**
		 * @deprecated 4.0.0
		 */
		openAsync(dropdown, options = {}) {
			// panel.deprecated(
			// 	"`pandel.dropdown`: opening via $dropdown won't return an async closure in future versions."
			// );

			return async (ready) => {
				await this.open(dropdown, options);

				// load all options from the dropdown
				const items = this.options();

				// react to empty dropdowns
				if (items.length === 0) {
					throw Error(`The dropdown is empty`);
				}

				ready(items);
			};
		},

		options() {
			// return an empty array for invalid/non-existing options
			if (Array.isArray(this.props.options) === false) {
				return [];
			}

			return this.props.options;
		},

		set(state) {
			// deprecated dropdown responses only return the options
			if (state.options) {
				// panel.deprecated(
				// 	"`pandel.dropdown`: responses should return the full state object. Only returning the options has been deprecated and will be removed in a future version."
				// );

				state.props = {
					options: state.options
				};
			}

			return parent.set.call(this, state);
		}
	});
};
