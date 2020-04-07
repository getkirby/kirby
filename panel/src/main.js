import App from "./App.vue";
import Filters from "./config/filters.js";
import Ui from "@/ui/index.js";
import Vue from "vue";
import Vuelidate from "vuelidate";
import I18n from "vuex-i18n";

Vue.config.productionTip = false;
Vue.config.devtools = true;

import "./config/components.js";
import "./config/api.js";
import "./config/errors.js";
import "./config/plugins.js";

import store from "./store/store.js";

Vue.use(I18n.plugin, store);
Vue.use(Ui);
Vue.use(Filters);
Vue.use(Vuelidate);

import router from "./config/router.js";

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
