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

/**
 * Create the Vue application
 */
const app = createApp(App);

/**
 * Create the Panel instance
 */
window.panel = Panel.create(app, window.panel.plugins);

/**
 * Global styles need to be loaded before
 * components
 */
import "./styles/config.css";
import "./styles/reset.css";

/**
 * Load all relevant Vue plugins
 * that do not depend on the Panel instance
 */
app.use(Helpers);
app.use(Libraries);
app.use(Store);
app.use(Components);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

/**
 * Some shortcuts to the Panel's features
 */
app.config.globalProperties.$dialog = window.panel.dialog.open.bind(
	window.panel.dialog
);
app.config.globalProperties.$drawer = window.panel.drawer.open.bind(
	window.panel.drawer
);
app.config.globalProperties.$dropdown = window.panel.dropdown.openAsync.bind(
	window.panel.dropdown
);
app.config.globalProperties.$go = window.panel.view.open.bind(
	window.panel.view
);
app.config.globalProperties.$reload = window.panel.reload.bind(window.panel);

/**
 * Additional functionalities and app configuration
 */
app.use(I18n);
app.use(ErrorHandling);
app.use(Legacy);

// container queries CSS polyfill
// TODO: remove when global support for container queries is reached
if (CSS.supports("container", "foo / inline-size") === false) {
	import("container-query-polyfill");
}

/**
 * Mount the Vue application
 */
app.mount("#app");
