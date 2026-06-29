import type { App } from "vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	install(app: App) {
		/**
		 * Handles promise rejections that have
		 * not been caught
		 */
		window.onunhandledrejection = (event) => {
			event.preventDefault();
			window.panel.error(event.reason);
		};

		/**
		 * Handles any Vue errors
		 */
		app.config.errorHandler = (err) => window.panel.error(err);
	}
};
