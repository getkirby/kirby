import Api from "@/app/plugins/api.js";
import App from "@/app/index.js";
import ErrorHandling from "@/app/plugins/errors.js";
import Go from "@/app/plugins/go.js";
import I18n from "@/app/plugins/i18n.js";
import Panel from "@/app/components/Panel.vue";
import Plugins from "@/app/plugins/plugins.js";
import Ui from "@/ui/index.js";
import Vue from "vue";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

import "./config/components.js";
import router from "./config/router.js";
import store from "@/app/store/index.js";

Vue.use(Api, store);
Vue.use(Plugins, store);
Vue.use(I18n, store);
Vue.use(Go, router);
Vue.use(Ui);
Vue.use(App);
Vue.use(ErrorHandling, store);
Vue.use(Vuelidate);

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
