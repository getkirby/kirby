import Vue from "vue";

export default {
  namespaced: true,
  state: {
    current: null,
    default: null,
    installed: []
  },
  mutations: {
    SET_CURRENT(state, id) {
      state.current = id;
    },
    SET_DEFAULT(state, id) {
      state.default = id;
    },
    INSTALL(state, translation) {
      state.installed[translation.id] = translation;
    }
  },
  actions: {
    async activate(context, id) {
      const translation = context.state.installed[id];

      // if translation is not yet install,
      // load from API, install translation and
      // then call this method again
      if (!translation) {
        const translation = await Vue.$model.translations.load(id);
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
    },
    default(context, id) {
      context.commit("SET_DEFAULT", id);
    },
    install(context, translation) {
      context.commit("INSTALL", translation);
      Vue.i18n.add(translation.id, translation.data);
    },
  }
};
