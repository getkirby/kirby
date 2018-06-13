import Vue from "vue";
import App from "./App.vue";

Vue.config.productionTip = false;

import "./config/ui.js";
import "./config/components.js";

import "./config/api.js";
import "./config/errors.js";
import "./config/i18n.js";
import "./config/plugins.js";

import cache from "./config/cache.js";
import router from "./config/router.js";
import store from "./config/store.js";
import access from "./config/access.js";

Vue.use(cache);
Vue.use(access);

window.panel.app = new Vue({
  router,
  store,
  render: h => {
    return h(App);
  }
}).$mount("#app");
