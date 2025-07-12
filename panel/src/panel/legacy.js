/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 *
 * @since 4.0.0
 * @deprecated 4.0.0
 */
export default {
	install(app) {
		const panel = window.panel;
		/**
		 * Some more shortcuts to the Panel's features
		 */
		app.config.globalProperties.$api = panel.api;
		app.config.globalProperties.$dialog = panel.dialog.open.bind(panel.dialog);
		app.config.globalProperties.$drawer = panel.drawer.open.bind(panel.drawer);
		app.config.globalProperties.$dropdown = panel.dropdown.openAsync.bind(
			panel.dropdown
		);
		app.config.globalProperties.$events = panel.events;
		app.config.globalProperties.$go = panel.view.open.bind(panel.view);
		app.config.globalProperties.$reload = panel.reload;
		app.config.globalProperties.$t = panel.$t = panel.t;
		app.config.globalProperties.$url = panel.url;
	}
};
