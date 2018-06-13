import Api from "@/api/api.js";
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
        router.push(context.state.path || "/");
        return user;
      });
    },
    logout(context) {
      Api.auth
        .logout()
        .then(() => {
          context.commit("SET_CURRENT", null);
          router.push("/login");
        })
        .catch(() => {
          context.commit("SET_CURRENT", null);
          router.push("/login");
        });
    },
    visit(context, path) {
      context.commit("SET_PATH", path);
    }
  }
};
