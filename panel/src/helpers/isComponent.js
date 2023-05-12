/**
 * Checks if the coponent is registered globally
 * @param {string} name component name
 * @returns {bool}
 */
export default (name) => {
	return typeof window.Vue.options.components[name] === "function";
};
