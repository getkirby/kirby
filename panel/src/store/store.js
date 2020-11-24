import Vue from "vue";
import Vuex from "vuex";

// store modules
import blocks from "./modules/blocks.js";
import content from "./modules/content.js";
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
    fatal: null,
    isLoading: false,
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
    SET_FATAL(state, html) {
      state.fatal = html;
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
    fatal(context, html) {
      context.commit("SET_FATAL", html);
    },
    isLoading(context, loading) {
      context.commit(loading === true ? "START_LOADING" : "STOP_LOADING");
    },
    title(context, title) {
      let site;

      if (context.state.user.current) {
        site = Vue.$api.site.get(["title"]);
      } else {
        site = new Promise(resolve => {
          resolve(context.state.system.info);
        });
      }

      site.then(site => {
        context.commit("SET_TITLE", title);
        context.dispatch("system/title", site.title);
        document.title = title || "";

        if (title !== null) {
          document.title += " | " + site.title;
        } else {
          document.title += site.title;
        }
      });
    },
    view(context, view) {
      context.commit("SET_VIEW", view);
    }
  },
  modules: {
    blocks: blocks,
    content: content,
    heartbeat: heartbeat,
    languages: languages,
    notification: notification,
    system: system,
    translation: translation,
    user: user
  }
});
