import Vue from "vue";

export default {
  namespaced: true,
  state: {
    all: [],
    current: null,
  },
  mutations: {
    SET_ALL(state, languages) {
      state.all = languages;
    },
    SET_CURRENT(state, language) {
      state.current = language;
    }
  },
  actions: {
    current(context, language) {
      context.commit("SET_CURRENT", language);
    },
    install(context, languages) {
      context.commit("SET_ALL", languages);
      context.commit("SET_CURRENT", languages[0]);
    }
  }
};
