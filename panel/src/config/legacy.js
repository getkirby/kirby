/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 */
export default {
	install(app) {
		/**
		 * @deprecated Use `window.panel.events`
		 */
		app.prototype.$events = window.panel.events;

		/**
		 * @deprecated Use `window.panel.vue`
		 */
		window.panel.$vue = window.panel.app = this;

		/**
		 * @deprecated Use `window.panel.notification.error` or throw an error
		 */
		window.panel.error = app.config.errorHandler;

		/**
		 * @deprecated Use `window.panel.notification.deprecated`
		 */
		window.panel.deprecated = (message) => {
			window.panel.notification.deprecated(message);
		};
	}
};
