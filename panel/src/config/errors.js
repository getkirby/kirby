export default {
  install(Vue, Store, Config) {

    Vue.config.errorHandler = error => {
      if (Config.debug) {
        window.console.error(error);
      }

      Store.dispatch("notification/error", {
        message: error.message || "An error occurred. Please reload the panel"
      });
    };

    window.panel = window.panel || {};
    window.panel.error = (notification, msg) => {
      if (Config.debug) {
        window.console.error(notification + ": " + msg);
      }

      Store.dispatch(
        "error",
        notification + ". See the console for more information."
      );
    };

  }
};
