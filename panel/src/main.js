import App from "./App.vue";
import Directives from "./config/directives.js";
import Filters from "./config/filters.js";
import Events from "./config/events.js";
import Vue from "vue";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

import "./config/components.js";
import "./config/api.js";
import "./config/errors.js";
import "./config/helpers.js";
import "./config/i18n.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Directives);
Vue.use(Events);
Vue.use(Filters);
Vue.use(Vuelidate);

import router from "./config/router.js";
import store from "./store/store.js";


 new Vue({
  router,
  store,
  created() {
    window.panel.app = this;

    // created plugin callbacks
    window.panel.plugins.created.forEach(plugin => {
      plugin(this);
    });
  },
  render: h => {
    return h(App);
  },
}).$mount("#app");
