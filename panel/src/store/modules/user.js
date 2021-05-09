export default function(app) {
  const $api = app.config.globalProperties.$api;

  return {
    namespaced: true,
    state() {
      return {
        current: null,
        path: null,
        pendingEmail: null,
        pendingChallenge: null
      }
    },
    mutations: {
      SET_CURRENT(state, user) {
        state.current = user;
        state.pendingEmail = null;
        state.pendingChallenge = null;

        if (user && user.permissions) {
          app.config.globalProperties.$user        = user;
          app.config.globalProperties.$permissions = user.permissions;
        } else {
          app.config.globalProperties.$user        = null;
          app.config.globalProperties.$permissions = null;
        }
      },
      SET_PATH(state, path) {
        state.path = path;
      },
      SET_PENDING(state, {email, challenge}) {
        state.pendingEmail = email;
        state.pendingChallenge = challenge;
        state.user = null;
        app.config.globalProperties.$user        = null;
        app.config.globalProperties.$permissions = null;
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
        const user = await $api.auth.user();
        context.commit("SET_CURRENT", user);
        return user;
      },
      login(context, user) {
        context.commit("SET_CURRENT", user);
        context.dispatch("translation/activate", user.language, { root: true });
        app.config.globalProperties.$go(context.state.path || "/");
        return user;
      },
      async logout(context, force) {
        context.commit("SET_CURRENT", null);

        if (force) {
          window.location.href = (window.panel.url || "") + "/login";
          return;
        }

        try {
          await $api.auth.logout();

        } finally {
          app.config.globalProperties.$go("/login");
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
  }
};
