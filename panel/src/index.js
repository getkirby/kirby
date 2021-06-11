import "vite/dynamic-import-polyfill";

import Vue from "vue";
import { component as App, plugin as Fiber } from "./config/fiber.js";
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
Vue.use(Fiber);
Vue.use(Api, store);

document.addEventListener("fiber:start", (e) => {
  if (e.detail.silent !== true) {
    store.dispatch("isLoading", true);
  }
});

document.addEventListener("fiber:finish", () => {
  if (Vue.$api.requests.length === 0) {
    store.dispatch("isLoading", false);
  }
});

new Vue({
  store,
  created() {
    window.panel.$vue = window.panel.app = this;
    window.panel.plugins.created.forEach((plugin) => plugin(this));
    this.$store.dispatch("content/init");
  },
  render: (h) => h(App)
}).$mount("#app");
