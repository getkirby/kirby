import Module from "./module.js";

export const defaults = () => {
	return {
		type: null,
		data: {}
	};
};

export default () => {
	const parent = Module("drag", defaults());

	return {
		...parent,

		/**
		 * Whether a drag state is currently set or not
		 *
		 * @returns {Boolean}
		 */
		get isDragging() {
			return this.type !== null;
		},

		/**
		 * Sets the drag state with data
		 * and a type for the data (e.g. text)
		 *
		 * @param {String} type
		 * @param {String|Object} data
		 */
		start(type, data) {
			this.type = type;
			this.data = data;
		},

		/**
		 * Clears the drag state
		 */
		stop() {
			this.type = null;
			this.data = {};
		}
	};
};
