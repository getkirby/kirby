import "vite/dynamic-import-polyfill"

import Vue from "vue";
import { default as App, plugin as Inertia } from "./inertia/app.js";
import Api from "./config/api.js";
import Events from "./config/events.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Vuelidate from "vuelidate";
import VuePortal from "@linusborg/vue-simple-portal";

import store from "./store/store.js";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Helpers);

import "./config/components.js";
import "./config/errors.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Events);
Vue.use(I18n);
Vue.use(Vuelidate);
Vue.use(VuePortal);
Vue.use(Inertia)
Vue.use(Api, store);

document.addEventListener("inertia:start", () => {
  store.dispatch("isLoading", true);
});

document.addEventListener("inertia:finish", () => {
  if (Vue.$api.requests.length === 0) {
    store.dispatch("isLoading", false);
  }
});

new Vue({
  store,
  created() {
    window.panel.$vue = window.panel.app = this;
    window.panel.plugins.created.forEach(plugin => plugin(this));
    this.$store.dispatch("content/init");
  },
  render: (h) => h(App)  
}).$mount("#app");
