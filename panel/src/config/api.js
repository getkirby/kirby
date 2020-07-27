import Api from "@/api/index.js";

export default {
  install(Vue, store) {
    Vue.prototype.$api = Vue.$api = Api({
      config: {
        endpoint: window.panel.$urls.api,
        onComplete: (requestId) => {
          Vue.$api.requests = Vue.$api.requests.filter(value => {
            return value !== requestId;
          });

          if (Vue.$api.requests.length === 0) {
            store.dispatch("isLoading", false);
          }
        },
        onError: error => {
          if (window.panel.$config.debug) {
            window.console.error(error);
          }

          // handle requests that return no auth
          if (
            error.code === 403 &&
            (error.message === "Unauthenticated" || error.key === "access.panel")
          ) {
            store.dispatch("user/logout", true);
          }
        },
        onPrepare: (options) => {
          // if language set, add to headers
          if (window.panel.$language) {
            options.headers["x-language"] = window.panel.$language.code;
          }

          // add the csrf token to every request
          options.headers["x-csrf"] = window.panel.$system.csrf;

          return options;
        },
        onStart: (requestId, silent = false) => {
          if (silent === false) {
            store.dispatch("isLoading", true);
          }

          Vue.$api.requests.push(requestId);
        },
        onSuccess: () => {
          clearInterval(Vue.$api.ping);
          Vue.$api.ping = setInterval(Vue.$api.auth.user, 5 * 60 * 1000);
        }
      },
      ping: null,
      requests: []
    });

    Vue.$api.ping = setInterval(Vue.$api.auth.user, 5 * 60 * 1000);
  }
};
