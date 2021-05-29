import Vue from "vue";
import Vuex from "vuex";

// store modules
import blocks from "./modules/blocks.js";
import content from "./modules/content.js";
import drawers from "./modules/drawers.js";
import heartbeat from "./modules/heartbeat.js";
import languages from "./modules/languages.js";
import notification from "./modules/notification.js";
import system from "./modules/system.js";
import translation from "./modules/translation.js";
import user from "./modules/user.js";

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
    breadcrumb() {
      // TODO: Remove in 3.7.0
      window.panel.deprecated("$store.disptach('breadcrumb') has been deprecated and removed.");
    },
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
    },
    title() {
      // TODO: Remove in 3.7.0
      window.panel.deprecated("$store.disptach('title') has been deprecated and removed.");
    },
    view() {
      // TODO: Remove in 3.7.0
      window.panel.deprecated("$store.disptach('view') has been deprecated and removed.");
    },
  },
  modules: {
    blocks: blocks,
    content: content,
    drawers: drawers,
    heartbeat: heartbeat,
    languages: languages,
    notification: notification,
    system: system,
    translation: translation,
    user: user
  }
});
