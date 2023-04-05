import Plugins from "@/panel/plugins.js";

export default {
	install(app) {
		/**
		 * Temporary polyfill until this is all
		 * bundled under window.panel
		 */
		window.panel.plugins = Plugins(app, window.panel.plugins);
	}
};
