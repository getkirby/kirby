/** Plugins */
import caniuse from "./plugins/caniuse.js";
import events from "./plugins/events.js";
import helpers from "./plugins/helpers.js";
import libraries from "./plugins/libraries.js";

export default {
  install(Vue) {

    Vue.prototype.$direction = "ltr";
    Vue.prototype.$debug = true;

    /** Plugins */
    Vue.use(caniuse);
    Vue.use(events);
    Vue.use(helpers);
    Vue.use(libraries);
  }
};
