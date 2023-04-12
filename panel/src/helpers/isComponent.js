import Vue from "vue";

/**
 * Checks if the coponent is registered globally
 * @param {string} name component name
 * @returns {bool}
 */
export default (name) => {
	return Object.hasOwn(Vue.options.components, name);
};
