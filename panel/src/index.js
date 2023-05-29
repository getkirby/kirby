import Vue, { h } from "vue";

import App from "./panel/app.js";
import Components from "./components/index.js";
import ErrorHandling from "./config/errorhandling";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Legacy from "./legacy/index.js";
import Libraries from "./libraries/index.js";
import Panel from "./panel/panel.js";
import store from "./store/store.js";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

/**
 * Create the Panel instance
 */
window.panel = Panel.create(window.panel.plugins);

/**
 * This is the single source of truth
 * for all Vue components.
 */
Vue.prototype.$panel = window.panel;

/**
 * Some shortcuts to the Panel's features
 */
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
 * Global styles need to be loaded before
 * components
 */
import "./styles/variables.css";
import "./styles/reset.css";
import "./styles/animations.css";

/**
 * Additional functionalities and app configuration
 */
Vue.use(ErrorHandling, window.panel);
Vue.use(Legacy, window.panel);
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(I18n);
Vue.use(Vuelidate);
Vue.use(Components);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

// :has() CSS polyfill
// TODO: remove when Firefox supports CSS :has
if (CSS.supports("selector(:has(*))") === false) {
	import("css-has-pseudo/browser").then(({ default: cssHas }) => {
		cssHas(document);
	});
}
// container queries CSS polyfill
// TODO: remove when global support for container queries is reached
if (CSS.supports("container") === false) {
	import("container-query-polyfill");
}

/**
 * Mount the Vue application
 */
window.panel.app.$mount("#app");
