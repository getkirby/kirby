/**
 * Checks if the coponent is registered globally
 * @param {string} name component name
 * @returns {bool}
 */
export default (name) => {
	const components = window.panel.app?._context.components ?? {};
	return Object.hasOwn(components, name);
};
