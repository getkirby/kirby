import store from "@/store/store.js";

export default {
  install(app) {
    window.panel = window.panel || {};

    // global rejected promise handler
    window.onunhandledrejection = (event) => {
      event.preventDefault();
      store.dispatch("notification/error", event.reason);
    };

    // global deprecation handler
    window.panel.deprecated = (message) => {
      store.dispatch("notification/deprecated", message);
    };

    // global error handler
    window.panel.error = app.config.errorHandler = (error) => {
      store.dispatch("notification/error", error);
    };
  }
};
