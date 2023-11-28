/**
 * @deprecated 4.0.0 Use window.panel.notification instead
 */
export default {
	namespaced: true,
	actions: {
		/**
		 * @deprecated 4.0.0 Use window.panel.notification.close() instead
		 */
		close() {
			window.panel.deprecated(
				"`$store.notification` will be removed in a future version. Use `$panel.notification` instead."
			);
			window.panel.notification.close();
		},
		/**
		 * @deprecated 4.0.0 Use window.panel.notification.deprecated() instead
		 */
		deprecated(context, message) {
			window.panel.deprecated(
				"`$store.notification.deprecated` will be removed in a future version. Use `$panel.deprecated` instead."
			);
			window.panel.notification.deprecated(message);
		},
		/**
		 * @deprecated 4.0.0 Use window.panel.notification.error() instead
		 */
		error(context, error) {
			window.panel.deprecated(
				"`$store.notification` will be removed in a future version. Use `$panel.notification` instead."
			);
			window.panel.notification.error(error);
		},
		/**
		 * @deprecated 4.0.0 Use window.panel.notification.open() instead
		 */
		open(context, payload) {
			window.panel.deprecated(
				"`$store.notification` will be removed in a future version. Use `$panel.notification` instead."
			);
			window.panel.notification.open(payload);
		},
		/**
		 * @deprecated 4.0.0 Use window.panel.notification.success() instead
		 */
		success(context, payload) {
			window.panel.deprecated(
				"`$store.notification` will be removed in a future version. Use `$panel.notification` instead."
			);
			window.panel.notification.success(payload);
		}
	}
};
