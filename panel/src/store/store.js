import Vue from "vue";
import Vuex from "vuex";

// store modules
import content from "./modules/content.js";
import drawers from "./modules/drawers.js";
import notification from "./modules/notification.js";

Vue.use(Vuex);

export default new Vuex.Store({
	// eslint-disable-next-line
	strict: process.env.NODE_ENV !== "production",
	actions: {
		/**
		 * @deprecated 4.0.0 Use window.panel.dialog.open()
		 */
		dialog(context, dialog) {
			window.panel.deprecated(
				"`$store.dialog` will be removed in a future version. Use `$panel.dialog.open()` instead."
			);
			window.panel.dialog.open(dialog);
		},
		/**
		 * @deprecated Use window.panel.drag.start(type, data)
		 */
		drag(context, drag) {
			window.panel.deprecated(
				"`$store.drag` will be removed in a future version. Use `$panel.drag.start(type, data)` instead."
			);
			window.panel.drag.start(...drag);
		},
		/**
		 * @deprecated Use window.panel.notification.fatal()
		 */
		fatal(context, options) {
			window.panel.deprecated(
				"`$store.fatal` will be removed in a future version. Use `$panel.notification.fatal()` instead."
			);
			window.panel.notification.fatal(options);
		},
		/**
		 * @deprecated 4.0.0 Use window.panel.isLoading
		 */
		isLoading(context, loading) {
			window.panel.deprecated(
				"`$store.isLoading` will be removed in a future version. Use `$panel.isLoading` instead."
			);
			window.panel.isLoading = loading;
		},
		/**
		 * @deprecated 4.0.0
		 */
		navigate() {
			window.panel.deprecated(
				"`$store.navigate` will be removed in a future version."
			);

			window.panel.dialog.close();
			window.panel.drawer.close();
		}
	},
	modules: {
		content: content,
		drawers: drawers,
		notification: notification
	}
});
