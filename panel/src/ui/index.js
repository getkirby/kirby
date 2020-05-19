/** Portal */
import VuePortal from "@linusborg/vue-simple-portal";

/** Plugins */
import caniuse from "./plugins/caniuse.js";
import events from "./plugins/events.js";
import helpers from "./plugins/helpers.js";
import libraries from "./plugins/libraries.js";
import components from "./components/index.js";

export default {
  install(Vue) {

    Vue.prototype.$direction = "ltr";
    Vue.prototype.$debug = true;

    /** Portal */
    Vue.use(VuePortal);

    /** Plugins */
    Vue.use(caniuse);
    Vue.use(events);
    Vue.use(libraries);
    Vue.use(helpers);

    /** Auto-load components */
    Vue.use(components);
  }
};
