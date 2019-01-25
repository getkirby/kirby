import App from "./App.vue";
import Directives from "./config/directives.js";
import Filters from "./config/filters.js";
import Events from "./config/events.js";
import Vue from "vue";
import Vuelidate from "vuelidate";

Vue.use(Directives);
Vue.use(Events);
Vue.use(Filters);
Vue.use(Vuelidate);

Vue.config.productionTip = false;
Vue.config.devtools = true;

import "./config/components.js";
import "./config/api.js";
import "./config/errors.js";
import "./config/i18n.js";
import "./config/plugins.js";

import router from "./config/router.js";
import store from "./store/store.js";

window.panel.app = new Vue({
  router,
  store,
  render: h => {
    return h(App);
  }
}).$mount("#app");
