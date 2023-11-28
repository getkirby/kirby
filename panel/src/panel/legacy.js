/* eslint-disable-next-line no-unused-vars */
import Vue from "vue";

/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 * @since 4.0.0
 *
 * @todo remove in v5
 */
export default {
	install(app) {
		/**
		 * Method object binding for the polyfills below
		 */
		window.panel.redirect = window.panel.redirect.bind(window.panel);
		window.panel.reload = window.panel.reload.bind(window.panel);
		window.panel.request = window.panel.request.bind(window.panel);
		window.panel.search = window.panel.search.bind(window.panel);

		/**
		 * @deprecated 4.0.0 Dollar Sign Shortcuts for panel data
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
			"view"
		];

		for (const polyfill of polyfills) {
			const key = `$${polyfill}`;
			app.prototype[key] = window.panel[key] = window.panel[polyfill];
		}

		// Kirbyup relies on this
		window.panel.$vue = window.panel.app;
	}
};
