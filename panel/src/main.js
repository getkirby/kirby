import App from "./App.vue";
import Api from "./config/api.js";
import Events from "./config/events.js";
import Helpers from "./helpers/index.js";
import Plugins from "./config/plugins.js";
import Vue from "vue";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;


import "./config/components.js";
import "./config/errors.js";

import caniuse from "./config/caniuse.js";
import libraries from "./config/libraries.js";

Vue.use(Events);
Vue.use(Helpers);
Vue.use(Vuelidate);
Vue.use(caniuse);
Vue.use(libraries);

import I18n from "./config/i18n.js";
import router from "./config/router.js";
import store from "./store/store.js";

Vue.use(Api, store);
Vue.use(I18n, store);
Vue.use(Plugins, store);

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
