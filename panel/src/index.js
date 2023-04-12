import Vue, { h, reactive } from "vue";

import Api from "./config/api.js";
import App from "./fiber/app.js";
import Components from "./components/index.js";
import ErrorHandling from "./config/errorhandling";
import Events from "./panel/events.js";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Legacy from "./config/legacy.js";
import Libraries from "./libraries/index.js";
import Notification from "./panel/notification.js";
import Panel from "./panel/panel.js";
import store from "./store/store.js";
import Translation from "./panel/translation.js";
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
		this.$panel.events = Events();
		this.$panel.isLoading = false;
		this.$panel.notification = Notification();
		this.$panel.searches = window.fiber.$searches;
		this.$panel.translation = Translation();

		/**
		 * shortcut for the translation method
		 */
		this.$panel.t = this.$panel.translation.translate.bind(
			this.$panel.translation
		);

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
import "./styles/variables.css";
import "./styles/reset.css";
import "./styles/animations.css";

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

app.$mount("#app");
