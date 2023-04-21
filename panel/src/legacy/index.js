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
		panel.reload = panel.view.reload.bind(panel.view);

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
			"reload",
			"request",
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
		 *
		 * @deprecated since 4.0
		 * @todo Refactor app features to use panel.{method} instead
		 */
		app.prototype.$dialog = dialog;
		app.prototype.$dropdown = dropdown;
		app.prototype.$go = go;
	}
};
