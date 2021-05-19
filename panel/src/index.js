import "vite/dynamic-import-polyfill"

import Vue from "vue";
import { App, plugin } from "@inertiajs/inertia-vue";
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
Vue.use(plugin)
Vue.use(Api, store);

const base = document.querySelector("base");

Vue.prototype.$go = function (path, options) {
  this.$inertia.visit(this.$url(path), options);
};

Vue.prototype.$reload = (options)   =>  Vue.prototype.$inertia.reload(options);
Vue.prototype.$url    = (path = "") => base.href + path.replace(/^\//, "");

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

    // created plugin callbacks
    window.panel.plugins.created.forEach(plugin => plugin(this));

    this.$store.dispatch("content/init");
  },
  render: (h) => {
    return h(App, {
      props: {
        initialPage: window.inertia,
        resolveComponent: (name) => Vue.component(name),
        transformProps: (props) => {

          /** Set translation */
          document.querySelector("html").setAttribute("lang", props.$translation.code);

          /** Set globals */
          Vue.prototype.$config      = window.panel.$config      = props.$config;
          Vue.prototype.$language    = window.panel.$language    = props.$language;
          Vue.prototype.$languages   = window.panel.$languages   = props.$languages;
          Vue.prototype.$permissions = window.panel.$permissions = props.$permissions;
          Vue.prototype.$system      = window.panel.$system      = props.$system;
          Vue.prototype.$translation = window.panel.$translation = props.$translation;
          Vue.prototype.$urls        = window.panel.$urls        = props.$urls;
          Vue.prototype.$user        = window.panel.$user        = props.$user;
          Vue.prototype.$view        = window.panel.$view        = props.$view;
          Vue.prototype.$views       = window.panel.$views       = {
            ...props.$views,
            ...window.panel.plugins.views
          };

          return props.$props;
        }
      },
    });
  }
}).$mount("#app");
