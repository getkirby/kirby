import Vue from "vue";

export default {
  namespaced: true,
  state: {
    current: null,
    path: null,
    pendingEmail: null,
    pendingChallenge: null
  },
  mutations: {
    SET_CURRENT(state, user) {
      state.current = user;
      state.pendingEmail = null;
      state.pendingChallenge = null;

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
    },
    SET_PENDING(state, {email, challenge}) {
      state.pendingEmail = email;
      state.pendingChallenge = challenge;
      state.user = null;
      Vue.prototype.$user = null;
      Vue.prototype.$permissions = null;
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
    async load(context) {
      const user = await Vue.$api.auth.user();
      context.commit("SET_CURRENT", user);
      return user;
    },
    login(context, user) {
      context.commit("SET_CURRENT", user);
      context.dispatch("translation/activate", user.language, { root: true });
      Vue.prototype.$go(context.state.path || "/");
      return user;
    },
    async logout(context, force) {
      context.commit("SET_CURRENT", null);

      if (force) {
        window.location.href = (window.panel.url || "") + "/login";
        return;
      }

      try {
        await Vue.$api.auth.logout();

      } finally {
        Vue.prototype.$go("/login");
      }
    },
    name(context, name) {
      context.commit("SET_CURRENT", {
        ...context.state.current,
        name: name
      });
    },
    pending(context, pending) {
      context.commit("SET_PENDING", pending);
    },
    visit(context, path) {
      context.commit("SET_PATH", path);
    }
  }
};
