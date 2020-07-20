import Vue from "vue";
import router from "@/config/router.js";

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
    async load(context) {
      const user = await Vue.$api.auth.user()
      context.commit("SET_CURRENT", user);
      return user;
    },
    async login(context, credentials) {
      const user = await  Vue.$api.auth.login(credentials);
      context.commit("SET_CURRENT", user);
      context.dispatch("translation/activate", user.language, { root: true });
      router.push(context.state.path || "/");
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
        router.push("/login");
      }
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
