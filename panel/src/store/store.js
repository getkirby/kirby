import Vue from "vue";
import Vuex from "vuex";

// store modules
import content from "./modules/content.js";
import heartbeat from "./modules/heartbeat.js";
import notification from "./modules/notification.js";

Vue.use(Vuex);

export default new Vuex.Store({
  // eslint-disable-next-line
  strict: process.env.NODE_ENV !== "production",
  state: {
    dialog: null,
    drag: null,
    isLoading: false,
    search: false,
  },
  mutations: {
    SET_DIALOG(state, dialog) {
      state.dialog = dialog;
    },
    SET_DRAG(state, drag) {
      state.drag = drag;
    },
    SET_SEARCH(state, search) {
      if (search === true) {
        search = {};
      }

      state.search = search;
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
    isLoading(context, loading) {
      context.commit(loading === true ? "START_LOADING" : "STOP_LOADING");
    },
    search(context, search) {
      context.commit("SET_SEARCH", search);
    }
  },
  modules: {
    content: content,
    heartbeat: heartbeat,
    notification: notification,
  }
});
