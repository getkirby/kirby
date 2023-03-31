import store from "@/store/store.js";

export default {
	install(app) {
		window.panel = window.panel || {};

		/**
		 * Handles promise rejections that have
		 * not been caught
		 *
		 * @param {Event} event
		 */
		window.onunhandledrejection = (event) => {
			event.preventDefault();
			store.dispatch("notification/error", event.reason);
		};

		// global deprecation handler
		window.panel.deprecated = (message) => {
			store.dispatch("notification/deprecated", message);
		};

		/**
		 * Handles any Vue errors
		 *
		 * @param {Error} error
		 */
		window.panel.error = app.config.errorHandler = (error) => {
			store.dispatch("notification/error", error);
		};
	}
};
