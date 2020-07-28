import { InertiaApp } from "@inertiajs/inertia-vue";
import Api from "./config/api.js";
import Filters from "./config/filters.js";
import Events from "./config/events.js";
import Helpers from "./helpers/index.js";
import Vue from "vue";
import I18n from "vuex-i18n";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Helpers);

import "./config/components.js";
import "./config/errors.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Events);
Vue.use(Filters);
Vue.use(Vuelidate);
Vue.use(InertiaApp);

import store from "./store/store.js";

Vue.use(I18n.plugin, store);
Vue.use(Api, store);

const base = document.querySelector("base");

Vue.prototype.$go = function (path, options) {
  this.$inertia.visit(this.$url(path), options);
};

Vue.prototype.$reload = function(options) {
  Vue.prototype.$inertia.reload(options);
};

Vue.prototype.$url = function (path = "") {
  return base.href + path.replace(/^\//, "");
};

document.addEventListener("inertia:visit", function () {
  store.dispatch("isLoading", true);
});

document.addEventListener("inertia:load", function () {
  store.dispatch("isLoading", false);
});

new Vue({
  store,
  created() {
    window.panel.$vue = window.panel.app = this;

    // created plugin callbacks
    window.panel.plugins.created.forEach((plugin) => {
      plugin(this);
    });

    this.$store.dispatch("content/init");
  },
  render: (h) => {
    return h(InertiaApp, {
      props: {
        initialPage: window.inertia,
        resolveComponent: (name) => require(`./components/Views/${name}`).default,
        transformProps: (props) => {

          /** Set translation */
          const lang = props.$translation.code;

          Vue.i18n.add(lang, props.$translation.data);
          Vue.i18n.set(lang);

          document.querySelector("html").setAttribute("lang", lang);

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
          Vue.prototype.$views       = window.panel.$views       = props.$views;

          return props.$props;

        }
      },
    });

  }
}).$mount("#app");
