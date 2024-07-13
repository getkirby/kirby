import { createApp } from "vue";

import App from "./panel/app.js";
import Components from "./components/index.js";
import ErrorHandling from "./config/errorhandling";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Legacy from "./panel/legacy.js";
import Libraries from "./libraries/index.js";
import Panel from "./panel/panel.js";
import Store from "./store/store.js";

import preserveListeners from "./mixins/preserveListeners.js";

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
app.use(Store);
app.use(Components);

/**
 * Add global mixins
 */
app.mixin(preserveListeners);

/**
 * Create the Panel instance
 */
window.panel = Panel.create(app, window.panel.plugins);

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
app.use(Legacy);

/**
 * Restore some Vue 2 functionality
 */
app.mixin({
	mounted() {
		this.$el.__vue__ = this;
	}
});

// container queries CSS polyfill
// TODO: remove when global support for container queries is reached
if (CSS.supports("container", "foo / inline-size") === false) {
	import("container-query-polyfill");
}

/**
 * Mount the Vue application
 */
app.mount("#app");
