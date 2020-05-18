export default {
  install(Vue, store) {

    Vue.config.errorHandler = error => {
      if (Vue.prototype.$config.debug) {
        window.console.error(error);
      }

      store.dispatch("notification/error", {
        message: error.message || "An error occurred. Please reload the Panel"
      });
    };

    window.panel = window.panel || {};
    window.panel.error = (notification, msg) => {
      if (Vue.prototype.$config.debug) {
        window.console.error(notification + ": " + msg);
      }

      store.dispatch(
        "error",
        notification + ". See the console for more information."
      );
    };

  }
};
