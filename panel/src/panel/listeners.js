import { isObject } from "@/helpers/object";

/**
 * @since 4.0.0
 */
export default () => {
	return {
		/**
		 * @param {String} event
		 * @param {Function} callback
		 */
		addEventListener(event, callback) {
			if (typeof callback === "function") {
				this.on[event] = callback;
			}
		},

		/**
		 * @param {Object}
		 */
		addEventListeners(listeners) {
			// ignore invalid listeners
			if (isObject(listeners) === false) {
				return;
			}

			for (const event in listeners) {
				this.addEventListener(event, listeners[event]);
			}
		},

		/**
		 * Emits an event
		 *
		 * @example
		 * panel.dialog.emit("submit", {})
		 *
		 * @param {String} event
		 * @param  {...any} args
		 * @returns {any}
		 */
		emit(event, ...args) {
			if (this.hasEventListener(event)) {
				return this.on[event](...args);
			}

			// return a dummy listener
			return () => {};
		},

		/**
		 * Checks if a listener exists
		 *
		 * @param {String} event
		 * @returns {Boolean}
		 */
		hasEventListener(event) {
			return typeof this.on[event] === "function";
		},

		listeners() {
			return this.on;
		},

		on: {}
	};
};
