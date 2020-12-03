import App from "./App.vue";
import Api from "./config/api.js";
import Events from "./config/events.js";
import I18n from "./config/i18n.js";
import Vue from "vue";
import Vuelidate from "vuelidate";
import Helpers from "./helpers/index.js";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Helpers);

import "./config/components.js";
import "./config/errors.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Events);
Vue.use(Vuelidate);

import VuePortal from "@linusborg/vue-simple-portal";
Vue.use(VuePortal);

import router from "./config/router.js";
import store from "./store/store.js";

Vue.use(Api, store);
Vue.use(I18n, store);

Vue.prototype.$go = (path) => {

  // support links with hash
  path = path.split("#");
  path = {
    path: path[0],
    hash: path[1] || null
  };

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
