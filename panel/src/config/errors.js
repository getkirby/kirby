import config from "./config.js";

export default {
  install(app) {
    app.config.errorHandler = error => {
      if (config.debug) {
        window.console.error(error);
      }
      
      app.config.globalProperties.$store.dispatch("notification/error", {
        message: error.message || "An error occurred. Please reload the Panel."
      });
    };
    
    window.panel = window.panel || {};
    window.panel.error = (notification, msg) => {
      if (config.debug) {
        window.console.error(notification + ": " + msg);
      }
    
      app.config.globalProperties.$store.dispatch(
        "error",
        notification + ". See the console for more information."
      );
    };
  }
};


