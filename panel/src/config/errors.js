export default {
	install(app) {
		window.panel = window.panel || {};

		// global rejected promise handler
		window.onunhandledrejection = (event) => {
			event.preventDefault();
			window.panel.$store.dispatch("notification/error", event.reason);
		};

		// global deprecation handler
		window.panel.deprecated = (message) => {
			window.panel.$store.dispatch("notification/deprecated", message);
		};

		// global error handler
		window.panel.error = app.config.errorHandler = (error) => {
			window.panel.$store.dispatch("notification/error", error);
		};
	}
};
