export default {
	install(app, panel) {
		/**
		 * Handles promise rejections that have
		 * not been caught
		 *
		 * @param {Event} event
		 */
		window.onunhandledrejection = (event) => {
			event.preventDefault();
			panel.error(event.reason);
		};

		/**
		 * Handles any Vue errors
		 */
		app.config.errorHandler = panel.error;
	}
};
