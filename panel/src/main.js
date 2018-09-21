import Vue from "vue";
import App from "./App.vue";

Vue.config.productionTip = false;
Vue.config.devtools = true;

import "./config/ui.js";
import "./config/components.js";

import "./config/api.js";
import "./config/errors.js";
import "./config/i18n.js";
import "./config/plugins.js";

import router from "./config/router.js";
import store from "./config/store.js";

window.panel.app = new Vue({
  router,
  store,
  render: h => {
    return h(App);
  }
}).$mount("#app");
