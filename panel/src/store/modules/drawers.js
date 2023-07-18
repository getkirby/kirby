/**
 * @deprecated 4.0.0 Use panel.drawer instead
 */
export default {
	namespaced: true,
	actions: {
		close(context, id) {
			window.panel.deprecated(
				"`$store.drawer` will be removed in a future version. Use `$panel.drawer` instead."
			);
			window.panel.drawer.close(id);
		},
		goto(context, id) {
			window.panel.deprecated(
				"`$store.drawer` will be removed in a future version. Use `$panel.drawer` instead."
			);
			window.panel.drawer.goto(id);
		},
		open(context, drawer) {
			window.panel.deprecated(
				"`$store.drawer` will be removed in a future version. Use `$panel.drawer` instead."
			);
			window.panel.drawer.goto(drawer);
		}
	}
};
