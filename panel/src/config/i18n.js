import { template } from "../helpers/string.js";

export default {
  install(app) {
    /**
     * Helper function for i18n strings
     */
    app.$t =
      app.prototype.$t =
      window.panel.$t =
        (key, data, fallback = null) => {
          if (typeof key !== "string") {
            return;
          }

          const string = window.panel.$translation.data[key] || fallback;

          if (typeof string !== "string") {
            return string;
          }

          return template(string, data);
        };

    /**
     * v-direction directive
     * only applies `:dir="$direciton"` if the
     * component isn't disabled
     */
    app.directive("direction", {
      inserted(el, binding, vnode) {
        if (vnode.context.disabled !== true) {
          el.dir = vnode.context.$direction;
        } else {
          el.dir = null;
        }
      }
    });
  }
};
