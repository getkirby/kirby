import Vue, { h } from "vue";

import Api from "./config/api.js";
import App from "./fiber/app.js";
import Components from "./components/index.js";
import Errors from "./config/errors.js";
import Events from "./config/events.js";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Libraries from "./libraries/index.js";
import Plugins from "./config/plugins.js";
import store from "./store/store.js";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

const app = new Vue({
	store,
	created() {
		window.panel.$vue = window.panel.app = this;
		window.panel.plugins.created.forEach((plugin) => plugin(this));
		this.$store.dispatch("content/init");
	},
	render: () => h(App)
});

// Global styles
import "./styles/config.css";
import "./styles/reset.css";

// Load functionalities
Vue.use(Errors);
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(Api, store);
Vue.use(Events);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Vuelidate);
Vue.use(Components);
Vue.use(Plugins);

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
