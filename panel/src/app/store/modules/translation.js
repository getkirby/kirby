import Vue from "vue";
import Api from "@/api/index.js";

export default {
  namespaced: true,
  state: {
    current: null,
    installed: []
  },
  mutations: {
    SET_CURRENT(state, id) {
      state.current = id;
    },
    INSTALL(state, translation) {
      state.installed[translation.id] = translation;
    }
  },
  actions: {
    async load(context, id) {
      return Api.translations.get(id);
    },
    install(context, translation) {
      context.commit("INSTALL", translation);
      Vue.i18n.add(translation.id, translation.data);
    },
    async activate(context, id) {
      const translation = context.state.installed[id];

      // if translation is not yet install,
      // load from API, install translation and
      // then call this method again
      if (!translation) {
        const translation = await context.dispatch("load", id);
        context.dispatch("install", translation);
        context.dispatch("activate", id);
        return;
      }

      // activate the translation
      Vue.i18n.set(id);

      // store the current translation
      context.commit("SET_CURRENT", id);

      // change the document's reading direction
      document.dir = translation.direction;

      // change the lang attribute on the html element
      document.documentElement.lang = id;
    }
  }
};
