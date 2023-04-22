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

			if (panel.debug) {
				console.error(event.reason);
			}

			panel.notification.error(event.reason);
		};

		/**
		 * Handles any Vue errors
		 *
		 * @param {Error} error
		 */
		app.config.errorHandler = (error) => {
			if (panel.debug) {
				console.error(error);
			}

			panel.notification.error(error);
		};
	}
};
