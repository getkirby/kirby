import Vue, { h, reactive } from "vue";

import Api from "./config/api.js";
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

const app = new Vue({
	store,
	created() {
		/**
		 * Shortcut to the panel for all components
		 */
		Vue.prototype.$panel = window.panel;

		/**
		 * Make the new panel temporarily available in the console
		 * to test features manually
		 */
		window.p = Panel.create(Vue, window.panel.plugins);

		/**
		 * Temporary polyfill until this is all
		 * bundled under window.panel
		 */
		this.$panel.plugins = window.p.plugins;

		/**
		 * This is temporary panel setup
		 * code until the entire panel.js class is there
		 */
		this.$panel.config = window.p.config;
		this.$panel.debug = window.p.debug;
		this.$panel.events = window.p.events;
		this.$panel.isLoading = window.p.isLoading;
		this.$panel.language = window.p.language;
		this.$panel.languages = window.p.languages;
		this.$panel.license = window.p.license;
		this.$panel.menu = window.p.menu;
		this.$panel.multilang = window.p.multilang;
		this.$panel.notification = window.p.notification;
		this.$panel.permissions = window.p.permissions;
		this.$panel.searches = window.p.searches;
		this.$panel.system = window.p.system;
		this.$panel.t = window.p.t;
		this.$panel.translation = window.p.translation;
		this.$panel.urls = window.p.urls;
		this.$panel.user = window.p.user;

		/**
		 * Make notification reactive. This will be done in
		 * the Panel object later
		 */
		reactive(this.$panel);

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

// Global styles
import "./styles/config.css";
import "./styles/reset.css";

// Load functionalities
Vue.use(ErrorHandling, window.panel);
Vue.use(Legacy);
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(Api, window.panel);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Vuelidate);
Vue.use(Components);

// Load CSS utilities after components
// to increase specificity
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

app.$mount("#app");
