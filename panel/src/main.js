import App from "./App.vue";
import Filters from "./config/filters.js";
import Events from "./config/events.js";
import Vue from "vue";
import Vuelidate from "vuelidate";
import Helpers from "./helpers/index.js";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Helpers);

import "./config/components.js";
import "./config/api.js";
import "./config/errors.js";
import "./config/i18n.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Events);
Vue.use(Filters);
Vue.use(Vuelidate);

import router from "./config/router.js";
import store from "./store/store.js";

Vue.prototype.$go = (path) => {
  router.push(path).catch(e => {
    if (e && e.name && e.name === "NavigationDuplicated") {
      return true;
    }

    throw e;
  });
};

new Vue({
  router,
  store,
  created() {
    window.panel.app = this;

    // created plugin callbacks
    window.panel.plugins.created.forEach(plugin => {
      plugin(this);
    });

    // initialize content store
    this.$store.dispatch("content/init");
  },
  render: h => {
    return h(App);
  },
}).$mount("#app");
