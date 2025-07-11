import { reactive } from "vue";
import { isObject } from "@/helpers/object";

/**
 * Represents a particular part of state
 * for the panel, i.e. system, translation.
 * Features are built upon such state objects
 *
 * The inheritance cascade is:
 * State -> Feature -> Modal
 *
 * @since 4.0.0
 *
 * @param {Object} panel The panel singleton
 * @param {String} key Sets the key for the state used by backend responses
 * @param {Object} defaults Sets the default state
 */
export default (key, defaults = {}) => {
	return reactive({
		/**
		 * State defaults will be reactive and
		 * must be present immediately in the object
		 * to get reactivity out of the box.
		 */
		...defaults,

		/**
		 * The key is used to place the state
		 * in the right place within the global
		 * panel state
		 *
		 * @returns {String}
		 */
		key() {
			return key;
		},

		/**
		 * Returns all default values.
		 * This will be used to restore the state
		 * and fetch the existing state.
		 *
		 * @returns {Object}
		 */
		defaults() {
			return defaults;
		},

		/**
		 * Restores the default state
		 */
		reset() {
			return this.set(this.defaults());
		},

		/**
		 * Sets a new state
		 *
		 * @param {Object} state
		 */
		set(state) {
			this.validateState(state);

			// merge the new state with the defaults
			// to always get a full object with all props
			for (const prop in this.defaults()) {
				this[prop] = state[prop] ?? this.defaults()[prop];
			}

			return this.state();
		},

		/**
		 * Returns the current state. The defaults
		 * object is used to fetch all keys from the object
		 * Keys which are not defined in the defaults
		 * object will also not be in the final state
		 *
		 * @returns {Object}
		 */
		state() {
			const state = {};

			for (const prop in this.defaults()) {
				state[prop] = this[prop] ?? this.defaults()[prop];
			}

			return state;
		},

		/**
		 * Validates the state object
		 *
		 * @param {Object} state
		 * @returns {Boolean}
		 */
		validateState(state) {
			if (isObject(state) === false) {
				throw new Error(`Invalid ${this.key()} state`);
			}

			return true;
		}
	});
};
