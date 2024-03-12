import Vue, { h } from "vue";

import App from "./panel/app.js";
import Components from "./components/index.js";
import ErrorHandling from "./config/errorhandling";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Legacy from "./panel/legacy.js";
import Libraries from "./libraries/index.js";
import Panel from "./panel/panel.js";
import store from "./store/store.js";

Vue.config.productionTip = false;
Vue.config.devtools = true;

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
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(Components);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

/**
 * Create the Panel instance
 *
 * This is the single source of truth
 * for all Vue components.
 */
window.panel = Vue.prototype.$panel = Panel.create(window.panel.plugins);

/**
 * Some shortcuts to the Panel's features
 */
Vue.prototype.$dialog = window.panel.dialog.open.bind(window.panel.dialog);
Vue.prototype.$drawer = window.panel.drawer.open.bind(window.panel.drawer);
Vue.prototype.$dropdown = window.panel.dropdown.openAsync.bind(
	window.panel.dropdown
);
Vue.prototype.$go = window.panel.view.open.bind(window.panel.view);
Vue.prototype.$reload = window.panel.reload.bind(window.panel);

/**
 * Create the Vue application
 */
window.panel.app = new Vue({
	store,
	render: () => h(App)
});

/**
 * Additional functionalities and app configuration
 */
Vue.use(I18n);
Vue.use(ErrorHandling);
Vue.use(Legacy);

// container queries CSS polyfill
// TODO: remove when global support for container queries is reached
if (CSS.supports("container", "foo / inline-size") === false) {
	import("container-query-polyfill");
}

/**
 * Mount the Vue application
 */
window.panel.app.$mount("#app");
