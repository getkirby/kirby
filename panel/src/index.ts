import { createApp } from "vue";

import App from "./panel/app";
import Components from "./components/index";
import ErrorHandling from "./config/errorhandling";
import Helpers from "./helpers/index";
import I18n from "./config/i18n";
import Legacy from "./panel/legacy";
import Libraries from "./libraries/index";
import Panel from "./panel/panel";
import SafeHtml from "./config/safeHtml";

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
 * Create the Panel instance
 */
Panel.create(app, window.panel.plugins);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

/**
 * Additional functionalities and app configuration
 */
app.use(I18n);
app.use(ErrorHandling);
app.use(SafeHtml);
app.use(Legacy);

/**
 * Restore some Vue 2 functionality
 */
app.mixin({
	mounted() {
		this.$el.__vue__ = this;
	}
});

/**
 * Mount the Vue application
 */
app.mount("#app");
