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
			window.panel.notification.error(event.reason);
		};

		// global deprecation handler
		window.panel.deprecated = (message) => {
			window.panel.notification.deprecated(message);
		};

		/**
		 * Handles any Vue errors
		 *
		 * @param {Error} error
		 */
		window.panel.error = app.config.errorHandler = (error) => {
			window.panel.notification.error(error);
		};
	}
};
