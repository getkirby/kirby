/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 */
export default {
	install(app) {
		/**
		 * @deprecated Access through `this.$panel` instead
		 */
		app.prototype.$api = window.panel.api;
		app.prototype.$events = window.panel.events;
		app.prototype.$t = window.panel.t;

		/**
		 * @deprecated Access through `window.panel` without dollar sign
		 */
		window.panel.$t = window.panel.t;
		window.panel.$vue = this;

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
