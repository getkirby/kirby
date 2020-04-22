/** Portal */
import VuePortal from "@linusborg/vue-simple-portal";

/** Plugins */
import events from "./plugins/events.js";
import helpers from "./plugins/helpers.js";
import libraries from "./plugins/libraries.js";

export default {
  install(Vue) {

    Vue.prototype.$direction = "ltr";
    Vue.prototype.$debug = true;

    /** Portal */
    Vue.use(VuePortal);

    /** Plugins */
    Vue.use(events);
    Vue.use(libraries);
    Vue.use(helpers);

    /** Auto-load components */
    const req = require.context('./components/', true, /\.vue$/i);
    req.keys().map(key => {
      const name = key.match(/\w+/)[0];
      return Vue.component("k-" + Vue.prototype.$helper.string.camelToKebab(name), req(key).default);
    });

  }
};
