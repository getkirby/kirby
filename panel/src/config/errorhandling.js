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

			if (window.panel.debug) {
				console.error(event.reason);
			}

			window.panel.notification.error(event.reason);
		};

		/**
		 * Handles any Vue errors
		 *
		 * @param {Error} error
		 */
		app.config.errorHandler = (error) => {
			if (window.panel.debug) {
				console.error(error);
			}

			window.panel.notification.error(error);
		};
	}
};
