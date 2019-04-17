import Vue from "vue";
import Api from "@getkirby/api-js";
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
    language(context, language) {
      context.dispatch("translation/activate", language, { root: true });
      context.commit("SET_CURRENT", {
        language: language,
        ...context.state.current
      });
    },
    load(context) {
      return Api.auth.user().then(user => {
        context.commit("SET_CURRENT", user);
        return user;
      });
    },
    login(context, credentials) {
      return Api.auth.login(credentials).then(user => {
        context.commit("SET_CURRENT", user);
        context.dispatch("translation/activate", user.language, { root: true });
        router.push(context.state.path || "/");
        return user;
      });
    },
    logout(context, force) {

      context.commit("SET_CURRENT", null);

      if (force) {
        window.location.href = (window.panel.url || "") + "/login";
        return;
      }

      Api.auth
        .logout()
        .then(() => {
          router.push("/login");
        })
        .catch(() => {
          router.push("/login");
        });
    },
    visit(context, path) {
      context.commit("SET_PATH", path);
    }
  }
};
