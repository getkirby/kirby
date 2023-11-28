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
			window.panel.error(event.reason);
		};

		/**
		 * Handles any Vue errors
		 */
		app.config.errorHandler = window.panel.error.bind(window.panel);
	}
};
