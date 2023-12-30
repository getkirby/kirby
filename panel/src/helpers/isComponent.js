/**
 * Checks if the coponent is registered globally
 * @param {string} name component name
 * @returns {bool}
 */
export default (name) => {
	return typeof window.Vue.options.components[name] === "function";
};

/**
 * Checks if the coponent is an instance of Vue
 * @param {object} component
 * @returns {bool}
 */
export function isVueComponent(component) {
	return typeof component?.render === "function";
}
