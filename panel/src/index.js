import Vue from "vue";

import Api from "./config/api.js";
import App from "./fiber/app.js";
import Components from "./components/index.js";
import Errors from "./config/errors.js";
import Events from "./config/events.js";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Libraries from "./config/libraries.js";
import store from "./store/store.js";

import Portal from "@linusborg/vue-simple-portal";
import Vuelidate from "vuelidate";


Vue.config.productionTip = false;
Vue.config.devtools = true;

// Global styles
import "./styles/variables.css"
import "./styles/reset.css"
import "./styles/utilities.css"

Vue.use(Errors);
Vue.use(Helpers);
Vue.use(Libraries);
Vue.use(Components);
Vue.use(Events);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Api, store);

Vue.use(Portal);
Vue.use(Vuelidate);

new Vue({
  store,
  created() {
    window.panel.$vue = window.panel.app = this;
    window.panel.plugins.created.forEach((plugin) => plugin(this));
    this.$store.dispatch("content/init");
  },
  render: (h) => h(App)
}).$mount("#app");
