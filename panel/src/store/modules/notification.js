/**
 * @deprecated Use window.panel.notification instead
 */
export default {
	namespaced: true,
	actions: {
		/**
		 * @deprecated Use window.panel.notification.close() instead
		 */
		close() {
			window.panel.notification.close();
		},
		/**
		 * @deprecated Use window.panel.notification.deprecated() instead
		 */
		deprecated(context, message) {
			window.panel.notification.deprecated(message);
		},
		/**
		 * @deprecated Use window.panel.notification.error() instead
		 */
		error(context, error) {
			window.panel.notification.error(error);
		},
		/**
		 * @deprecated Use window.panel.notification.open() instead
		 */
		open(context, payload) {
			window.panel.notification.open(payload);
		},
		/**
		 * @deprecated Use window.panel.notification.success() instead
		 */
		success(context, payload) {
			window.panel.notification.success(payload);
		}
	}
};
