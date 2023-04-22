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
		 * @deprecated Use window.panel.dialog.open()
		 */
		dialog(context, dialog) {
			window.panel.dialog.open(dialog);
		},
		/**
		 * @deprecated Use window.panel.drag
		 */
		drag(context, drag) {
			window.panel.drag = drag;
		},
		/**
		 * @deprecated Use window.panel.notification.fatal()
		 */
		fatal(context, options) {
			window.panel.notification.fatal(options);
		},
		/**
		 * @deprecated Use window.panel.isLoading
		 */
		isLoading(context, loading) {
			window.panel.isLoading = loading;
		},
		/**
		 * @deprecated
		 */
		navigate(context) {
			window.panel.dialog.close();
			context.dispatch("drawers/close");
		}
	},
	modules: {
		content: content,
		drawers: drawers,
		notification: notification
	}
});
