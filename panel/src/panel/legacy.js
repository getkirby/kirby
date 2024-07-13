/**
 * This is the graveyard for all deprecated
 * aliases. We can remove them step by step
 * in future major releases to clean up.
 *
 * @since 4.0.0
 * @deprecated 4.0.0
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
			app.config.globalProperties[key] = window.panel[key] =
				window.panel[polyfill];
		}

		/**
		 * Some more shortcuts to the Panel's features
		 */
		app.config.globalProperties.$dialog = window.panel.dialog.open.bind(
			window.panel.dialog
		);
		app.config.globalProperties.$drawer = window.panel.drawer.open.bind(
			window.panel.drawer
		);
		app.config.globalProperties.$dropdown =
			window.panel.dropdown.openAsync.bind(window.panel.dropdown);
		app.config.globalProperties.$go = window.panel.view.open.bind(
			window.panel.view
		);
		app.config.globalProperties.$reload = window.panel.reload.bind(
			window.panel
		);
	}
};
