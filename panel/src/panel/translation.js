import { reactive } from "vue";
import { template } from "@/helpers/string.js";
import State from "./state.js";

export const defaults = () => {
	return {
		code: null,
		data: {},
		direction: "ltr",
		name: null,
		weekday: 1
	};
};

/**
 * Represents the interface language
 * for the current user
 *
 * @since 4.0.0
 */
export default () => {
	const parent = State("translation", defaults());

	return reactive({
		...parent,

		/**
		 * When the active state of a translation
		 * changes, the document language and reading
		 * direction will also be updated
		 *
		 * @param {Object} state
		 * @returns {Object} The new state
		 */
		set(state) {
			parent.set.call(this, state);

			/**
			 * Update the document language for better accessibility
			 */
			document.documentElement.lang = this.code;

			/**
			 * Some elements – i.e. drag ghosts -
			 * are injected into the body and not the panel div.
			 * They need the dir to be displayed correctly
			 */
			document.body.dir = this.direction;

			return this.state();
		},

		/**
		 * Fetches a translation string and
		 * can optionally replace placeholders
		 * with values from the data object
		 *
		 * @param {String} key
		 * @param {Object} data
		 * @param {String} fallback
		 * @returns {String}
		 */
		translate(key, data, fallback = null) {
			if (typeof key !== "string") {
				return;
			}

			const string = this.data[key] ?? fallback;

			if (typeof string !== "string") {
				return string;
			}

			return template(string, data);
		}
	});
};
