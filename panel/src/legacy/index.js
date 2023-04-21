import dialog from "@/fiber/dialog.js";
import dropdown from "./dropdown.js";
import go from "./go.js";

/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 */
export default {
	install(app, panel) {
		/**
		 * @deprecated Deprecated Panel Methods
		 */
		panel.error = app.config.errorHandler;
		panel.deprecated = panel.notification.deprecated.bind(panel.notification);

		/**
		 * Method object binding for the polyfills below
		 */
		panel.redirect = panel.redirect.bind(panel);
		panel.reload = panel.reload.bind(panel);
		panel.request = panel.request.bind(panel);
		panel.search = panel.search.bind(panel);

		/**
		 * @deprecated Dollar Sign Shortcuts
		 *
		 * @example
		 * // Old:
		 * `window.panel.$config`
		 * // New:
		 * window.panel.config
		 *
		 * @example
		 * // Old:
		 * this.$config
		 * // New
		 * this.$panel.config
		 */
		const polyfills = [
			"api",
			"config",
			"direction",
			"events",
			"language",
			"languages",
			"license",
			"menu",
			"multilang",
			"permissions",
			"search",
			"searches",
			"system",
			"t",
			"translation",
			"url",
			"urls",
			"user",
			"view",
			"vue"
		];

		for (const polyfill of polyfills) {
			const key = `$${polyfill}`;
			app.prototype[key] = panel[key] = panel[polyfill];
		}

		/**
		 * Shortcut methods
		 */
		app.prototype.$dialog = dialog;
		app.prototype.$dropdown = dropdown;
		app.prototype.$go = go;
	}
};
