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
	state: {
		drag: null
	},
	mutations: {
		SET_DRAG(state, drag) {
			state.drag = drag;
		}
	},
	actions: {
		/**
		 * @deprecated Use window.panel.dialog.open()
		 */
		dialog(context, dialog) {
			window.panel.dialog.open(dialog);
		},
		drag(context, drag) {
			context.commit("SET_DRAG", drag);
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
