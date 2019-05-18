import Vue from "vue";
import Vuex from "vuex";

// store modules
import form from "./modules/form.js";
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
    breadcrumb: [],
    dialog: null,
    drag: null,
    isLoading: false,
    search: false,
    title: null,
    view: null
  },
  mutations: {
    SET_BREADCRUMB(state, breadcrumb) {
      state.breadcrumb = breadcrumb;
    },
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
    SET_TITLE(state, title) {
      state.title = title;
    },
    SET_VIEW(state, view) {
      state.view = view;
    },
    START_LOADING(state) {
      state.isLoading = true;
    },
    STOP_LOADING(state) {
      state.isLoading = false;
    }
  },
  actions: {
    breadcrumb(context, breadcrumb) {
      context.commit("SET_BREADCRUMB", breadcrumb);
    },
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
    },
    title(context, title) {
      context.commit("SET_TITLE", title);
      document.title = title || "";

      if (context.state.system.info.title) {
        if (title !== null) {
          document.title += " | " + context.state.system.info.title;
        } else {
          document.title += context.state.system.info.title;
        }
      }
    },
    view(context, view) {
      context.commit("SET_VIEW", view);
    }
  },
  modules: {
    form: form,
    heartbeat: heartbeat,
    languages: languages,
    notification: notification,
    system: system,
    translation: translation,
    user: user
  }
});
