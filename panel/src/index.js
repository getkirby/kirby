import { InertiaApp } from "@inertiajs/inertia-vue";
import Api from "./config/api.js";
import Events from "./config/events.js";
import I18n from "./config/i18n.js";
import Vue from "vue";
import Vuelidate from "vuelidate";
import Helpers from "./helpers/index.js";

import str from "../helpers/string.js";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Helpers);

import "./config/components.js";
import "./config/errors.js";
import "./config/libraries.js";
import "./config/plugins.js";

Vue.use(Events);
Vue.use(Vuelidate);

import VuePortal from "@linusborg/vue-simple-portal";
Vue.use(VuePortal);
Vue.use(InertiaApp);

import store from "./store/store.js";

Vue.use(Api, store);
Vue.use(I18n, store);

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

new Vue({
  store,
  created() {
    window.panel.$vue = window.panel.app = this;

    // created plugin callbacks
    window.panel.plugins.created.forEach(plugin => plugin(this));

    this.$store.dispatch("content/init");
  },
  render: (h) => {
    return h(InertiaApp, {
      props: {
        initialPage: window.inertia,
        resolveComponent: (name) => import(`./components/Views/${name}`),
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
          Vue.prototype.$t            = window.panel.$views      = (key, data) => {
            if (typeof key !== 'string') {
              return;
            }
          
            const string = window.panel.$translation.data[key];
            return str.template(string, data);
          }
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
