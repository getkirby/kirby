import Vue from "vue";
import Vuex from "vuex";

// store modules
import content from "./modules/content.js";
import drawers from "./modules/drawers.js";
import notification from "./modules/notification.js";

Vue.use(Vuex);

export default new Vuex.Store({
  // eslint-disable-next-line
  strict: process.env.NODE_ENV !== "production",
  state: {
    dialog: null,
    drag: null,
    fatal: false,
    isLoading: false
  },
  mutations: {
    SET_DIALOG(state, dialog) {
      state.dialog = dialog;
    },
    SET_DRAG(state, drag) {
      state.drag = drag;
    },
    SET_FATAL(state, html) {
      state.fatal = html;
    },
    SET_LOADING(state, loading) {
      state.isLoading = loading;
    }
  },
  actions: {
    dialog(context, dialog) {
      context.commit("SET_DIALOG", dialog);
    },
    drag(context, drag) {
      context.commit("SET_DRAG", drag);
    },
    fatal(context, options) {
      // close the fatal window if false
      // is passed as options
      if (options === false) {
        context.commit("SET_FATAL", false);
        return;
      }

      console.error("The JSON response could not be parsed");

      // show the full response in the console
      // if debug mode is enabled
      if (window.panel.$config.debug) {
        console.info(options.html);
      }

      // only show the fatal dialog if the silent
      // option is not set to true
      if (!options.silent) {
        context.commit("SET_FATAL", options.html);
      }
    },
    isLoading(context, loading) {
      context.commit("SET_LOADING", loading === true);
    },
    navigate(context) {
      context.dispatch("dialog", null);
      context.dispatch("drawers/close");
    }
  },
  modules: {
    content: content,
    drawers: drawers,
    notification: notification
  }
});
