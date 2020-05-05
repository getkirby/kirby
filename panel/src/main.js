import Api from "./config/api.js";
import App from "./App.vue";
import Components from "./config/components.js";
import Config from "./config/config.js";
import ErrorHandling from "./config/errors.js";
import I18n from "vuex-i18n";
import Models from "./config/models.js";
import Plugins from "./config/plugins.js";
import Router from "./config/router.js";
import Store from "./store/store.js";
import Ui from "@/ui/index.js";
import Vue from "vue";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(I18n.plugin, Store);
Vue.use(Ui);
Vue.use(Components);
Vue.use(ErrorHandling);
Vue.use(Api, Store, Config);
Vue.use(Models);
Vue.use(Plugins, Store);

new Vue({
  router: Router(Vue, Store, Config),
  store: Store,
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
