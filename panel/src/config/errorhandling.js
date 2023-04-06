export default {
	install(app) {
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

		/**
		 * Handles any Vue errors
		 *
		 * @param {Error} error
		 */
		app.config.errorHandler = (error) => {
			window.panel.notification.error(error);
		};
	}
};
