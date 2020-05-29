import Vue from "vue";

export default {
  namespaced: true,
  state: {
    current: null,
    path: null
  },
  mutations: {
    SET_CURRENT(state, user) {
      state.current = user;

      if (user && user.permissions) {
        Vue.prototype.$user        = user;
        Vue.prototype.$permissions = user.permissions;
      } else {
        Vue.prototype.$user = null;
        Vue.prototype.$permissions = null;
      }
    },
    SET_PATH(state, path) {
      state.path = path;
    }
  },
  actions: {
    current(context, user) {
      context.commit("SET_CURRENT", user);
    },
    email(context, email) {
      context.commit("SET_CURRENT", {
        ...context.state.current,
        email: email
      });
    },
    language(context, language) {
      context.dispatch("translation/activate", language, { root: true });
      context.commit("SET_CURRENT", {
        ...context.state.current,
        language: language,
      });
    },
    name(context, name) {
      context.commit("SET_CURRENT", {
        ...context.state.current,
        name: name
      });
    },
    visit(context, path) {
      context.commit("SET_PATH", path);
    }
  }
};
