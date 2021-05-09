import Api from "@/api/index.js";
import config from "./config.js";

export default {
  install(app) {
    const api = Api({
      config: {
        endpoint: config.api,
        onComplete: (requestId) => {
          const api = app.config.globalProperties.$api;
          api.requests = api.requests.filter(value => {
            return value !== requestId;
          });

          if (api.requests.length === 0) {
            const store = app.config.globalProperties.$store;
            store.dispatch("isLoading", false);
          }
        },
        onError: error => {
          if (config.debug) {
            window.console.error(error);
          }

          // handle requests that return no auth
          if (
            error.code === 403 &&
            (error.message === "Unauthenticated" || error.key === "access.panel")
          ) {
            const store = app.config.globalProperties.$store;
            store.dispatch("user/logout", true);
          }
        },
        onParserError: (result) => {
          const store = app.config.globalProperties.$store;
          store.dispatch("fatal", result);
          throw new Error("The JSON response from the API could not be parsed");
        },
        onPrepare: (options) => {
          // if language set, add to headers
          const store = app.config.globalProperties.$store;
          if (store.state.languages.current) {
            options.headers["x-language"] = store.state.languages.current.code;
          }

          // add the csrf token to every request
          options.headers["x-csrf"] = window.panel.csrf;

          return options;
        },
        onStart: (requestId, silent = false) => {
          if (silent === false) {
            const store = app.config.globalProperties.$store;
            store.dispatch("isLoading", true);
          }

          app.config.globalProperties.$api.requests.push(requestId);
        },
        onSuccess: () => {
          const api = app.config.globalProperties.$api;
          clearInterval(api.ping);
          api.ping = setInterval(api.auth.user, 5 * 60 * 1000);
        }
      },
      ping: null,
      requests: []
    });

    api.ping = setInterval(api.auth.user, 5 * 60 * 1000);
    
    app.config.globalProperties.$api = api;
  }
};
