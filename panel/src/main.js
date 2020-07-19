import App from "./App.vue";
import Api from "./config/api.js";
import Helpers from "./helpers/index.js";
import I18n from "@/app/plugins/i18n.js";
import Panel from "@/app/components/Panel.vue";
import Plugins from "@/app/plugins/plugins.js";
import Ui from "@/ui/index.js";
import Vue from "vue";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;


import "./config/components.js";
import "./config/errors.js";
import router from "./config/router.js";
import store from "./store/store.js";

Vue.use(Api, store);
Vue.use(Plugins, store);
Vue.use(I18n, store);
Vue.use(Helpers);
Vue.use(Ui);
Vue.use(App);
Vue.use(Vuelidate);

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
    this.$plugins.created.forEach(plugin => {
      plugin(this);
    });

    // initialize content store
    this.$store.dispatch("content/init");
  },
  render: h => {
    return h(Panel);
  },
}).$mount("#app");
