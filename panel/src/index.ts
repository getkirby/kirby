/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */

import { createApp } from "vue";

import App from "./panel/app";
import Components from "./config/components";
import ErrorHandling from "./config/errorhandling";
import Helpers from "./helpers/index";
import I18n from "./config/i18n";
import Legacy from "./panel/legacy";
import Libraries from "./libraries/index";
import Panel from "./panel/panel";
import SafeHtml from "./config/safeHtml";
import { load as Plugins } from "./panel/plugins";

import preserveDataAttrs from "./mixins/preserveDataAttrs";
import preserveListeners from "./mixins/preserveListeners";

/**
 * Global styles need to be loaded before
 * components
 */
import "./styles/config.css";
import "./styles/reset.css";

/**
 * Create the Vue application
 */
const app = createApp(App);

/**
 * Load all relevant Vue plugins
 * that do not depend on the Panel instance
 */
app.use(Helpers);
app.use(Libraries);
app.use(Components);

/**
 * Add global mixins
 */
app.mixin(preserveDataAttrs);
app.mixin(preserveListeners);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

/**
 * Creates the Panel instance and mounts the app
 */
function mount(): void {
	Panel.create(app, window.panel.plugins);

	// additional functionalities and app configuration
	app.use(I18n);
	app.use(ErrorHandling);
	app.use(SafeHtml);
	app.use(Legacy);

	// restore some Vue 2 functionality
	app.mixin({
		mounted() {
			this.$el.__vue__ = this;
		}
	});

	app.mount("#app");
}

/**
 * Load the plugins once the core components are registered,
 * then mount the Panel app
 */
Plugins(window.panelPlugins).then(mount);
