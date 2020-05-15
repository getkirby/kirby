import { App, Router, Store } from "@/app/index.js";
import Component from "./App.vue";
import Ui from "@/ui/index.js";
import Vue from "vue";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Ui);
Vue.use(App);

new Vue({
  router: Router,
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
    return h(Component);
  },
}).$mount("#app");
