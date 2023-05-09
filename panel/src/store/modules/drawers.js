/**
 * @deprecated Use panel.drawer instead
 */
export default {
	namespaced: true,
	actions: {
		close(context, id) {
			window.panel.drawer.close(id);
		},
		goto(context, id) {
			window.panel.drawer.goto(id);
		},
		open(context, drawer) {
			window.panel.drawer.goto(drawer);
		}
	}
};
