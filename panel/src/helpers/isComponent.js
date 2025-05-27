/**
 * Checks if the component is registered globally
 * @param {string} name component name
 * @param {Vue} app Vue instance (if not provided, window.panel.app will be used)
 * @returns {bool}
 */
export default (name, app) => {
	app ??= window.panel?.app;
	const components = app?._context.components ?? {};
	return Object.hasOwn(components, name);
};
