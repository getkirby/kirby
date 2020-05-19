import App from "@/app/index.js";
import Api from "@/app/plugins/api.js";
import Config from "@/app/plugins/config.js";
import ErrorHandling from "@/app/plugins/errors.js";
import I18n from "vuex-i18n";
import Models from "@/app/plugins/models.js";
import Panel from "@/app/Panel.vue";
import Plugins from "@/app/plugins/plugins.js";
import Router from "@/app/plugins/router.js";
import Store from "@/app/store/store.js";
import Ui from "@/ui/index.js";
import Vue from "vue";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Config);
Vue.use(I18n.plugin, Store);
Vue.use(Ui);
Vue.use(App);
Vue.use(ErrorHandling, Store);
Vue.use(Api, Store);
Vue.use(Models, Store);
Vue.use(Plugins, Store);

new Vue({
  router: Router(Vue, Store),
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
    return h(Panel);
  },
}).$mount("#app");
