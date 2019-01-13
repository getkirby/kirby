import Api from "@/api/api.js";

export default {
  namespaced: true,
  state: {
    info: {
      title: null
    }
  },
  mutations: {
    SET_INFO(state, info) {
      state.info = info;
    },
    SET_LICENSE(state, license) {
      state.info.license = license;
    },
    SET_TITLE(state, title) {
      state.info.title = title;
    }
  },
  actions: {
    title(context, title) {
      context.commit("SET_TITLE", title);
    },
    register(context, license) {
      context.commit("SET_LICENSE", license);
    },
    load(context, reload) {
      // reuse the cached system info
      if (
        !reload &&
        context.state.info.isReady &&
        context.rootState.user.current
      ) {
        return new Promise(resolve => {
          resolve(context.state.info);
        });
      }

      // reload the system info
      return Api.system
        .info({ view: "panel" })
        .then(info => {
          context.commit("SET_INFO", {
            isReady: info.isInstalled && info.isOk,
            ...info
          });

          if (info.languages) {
            context.dispatch("languages/install", info.languages, {
              root: true
            });
          }

          context.dispatch("translation/install", info.translation, {
            root: true
          });
          context.dispatch("translation/activate", info.translation.id, {
            root: true
          });

          if (info.user) {
            context.dispatch("user/current", info.user, { root: true });
          }

          return context.state.info;
        })
        .catch(error => {
          context.commit("SET_INFO", {
            isBroken: true,
            error: error.message
          });
        });
    }
  }
};
