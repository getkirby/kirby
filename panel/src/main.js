import Vue from "vue";
import App from "./App.vue";

Vue.config.productionTip = false;
Vue.config.devtools = true;

import Errors from "./config/errors.js";
Vue.use(Errors);

import Helpers from "./helpers/index.js";
Vue.use(Helpers);

import Libraries from "./config/libraries.js";
Vue.use(Libraries);

import Events from "./config/events.js";
Vue.use(Events);

import Plugins from "./config/plugins.js";
Vue.use(Plugins);

import Vuelidate from "vuelidate";
Vue.use(Vuelidate);

import VuePortal from "@linusborg/vue-simple-portal";
Vue.use(VuePortal);

import "./config/components.js";
import "./config/i18n.js";

import Api from "./config/api.js";

import router from "./config/router.js";
import store from "./store/store.js";

Vue.use(Api, store);

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
    window.panel.plugins.created.forEach(plugin => plugin(this));

    // initialize content store
    this.$store.dispatch("content/init");
  },
  render: h => {
    return h(App);
  },
}).$mount("#app");
