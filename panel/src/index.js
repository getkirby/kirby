import Vue, { h } from "vue";

import Api from "./config/api.js";
import App from "./fiber/app.js";
import Errors from "./config/errors.js";
import Events from "./config/events.js";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Libraries from "./libraries/index.js";
import Plugins from "./config/plugins.js";
import Stores from "./stores/index.js";

import Portal from "@linusborg/vue-simple-portal";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Stores);
Vue.use(Errors);
Vue.use(Helpers);
Vue.use(Libraries);

// Global styles
import "./styles/variables.css";
import "./styles/reset.css";
import "./styles/animations.css";

// Load components
import "./config/components.js";

// Load utilities after components
// to increase specificity
import "./styles/utilities.css";

Vue.use(Plugins);
Vue.use(Events);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Api);

Vue.use(Portal);
Vue.use(Vuelidate);

const app = new Vue({
	created() {
		window.panel.$vue = window.panel.app = this;
		window.panel.plugins.created.forEach((plugin) => plugin(this));
		this.$store.dispatch("content/init");
	},
	render: () => h(App)
});

app.$mount("#app");
