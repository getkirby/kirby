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
		app.prototype.$api = panel.api;
		app.prototype.$dialog = panel.dialog.open.bind(panel.dialog);
		app.prototype.$drawer = panel.drawer.open.bind(panel.drawer);
		app.prototype.$dropdown = panel.dropdown.openAsync.bind(panel.dropdown);
		app.prototype.$events = panel.events;
		app.prototype.$go = panel.view.open.bind(panel.view);
		app.prototype.$reload = panel.reload;
		app.prototype.$t = panel.$t = panel.t;
		app.prototype.$url = panel.url;
	}
};
