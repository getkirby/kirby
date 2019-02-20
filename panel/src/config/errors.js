import Vue from "vue";
import store from "@/store/store.js";
import config from "./config.js";

Vue.config.errorHandler = error => {
  if (config.debug) {
    window.console.error(error);
  }

  store.dispatch("notification/error", {
    message: error.message || "An error occurred. Please reload the panel"
  });
};

window.panel = window.panel || {};
window.panel.error = (notification, msg) => {
  if (config.debug) {
    window.console.error(notification + ": " + msg);
  }

  store.dispatch(
    "error",
    notification + ". See the console for more information."
  );
};
