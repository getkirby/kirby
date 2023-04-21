import Vue, { h } from "vue";

import App from "./fiber/app.js";
import Components from "./components/index.js";
import ErrorHandling from "./config/errorhandling";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Legacy from "./config/legacy.js";
import Libraries from "./libraries/index.js";
import Panel from "./panel/panel.js";
import store from "./store/store.js";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

/**
 * Make Vue accessible globally
 */
window.Vue = Vue;

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
 * Create the Vue application
 */
window.panel.app = new Vue({
	store,
	created() {
		/**
		 * Delegate all required window events to the
		 * event emitter
		 */
		this.$panel.events.subscribe();

		/**
		 * Register all created plugins
		 */
		this.$panel.plugins.created.forEach((plugin) => plugin(this));

		this.$store.dispatch("content/init");
	},
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
Vue.use(Legacy);
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Vuelidate);
Vue.use(Components);

/**
 * Load CSS utilities after components
 * to increase specificity
 */
import "./styles/utilities.css";

/**
 * Mount the Vue application
 */
window.panel.app.$mount("#app");
