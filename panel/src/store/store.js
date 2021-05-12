import Vue from "vue";
import Vuex from "vuex";

// store modules
import blocks from "./modules/blocks.js";
import content from "./modules/content.js";
import drawers from "./modules/drawers.js";
import heartbeat from "./modules/heartbeat.js";
import notification from "./modules/notification.js";

Vue.use(Vuex);

export default new Vuex.Store({
  // eslint-disable-next-line
  strict: process.env.NODE_ENV !== "production",
  state: {
    dialog: null,
    drag: null,
    fatal: null,
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
    START_LOADING(state) {
      state.isLoading = true;
    },
    STOP_LOADING(state) {
      state.isLoading = false;
    }
  },
  actions: {
    dialog(context, dialog) {
      context.commit("SET_DIALOG", dialog);
    },
    drag(context, drag) {
      context.commit("SET_DRAG", drag);
    },
    fatal(context, html) {
      context.commit("SET_FATAL", html);
    },
    isLoading(context, loading) {
      context.commit(loading === true ? "START_LOADING" : "STOP_LOADING");
    }
  },
  modules: {
    blocks: blocks,
    content: content,
    drawers: drawers,
    heartbeat: heartbeat,
    notification: notification
  }
});
