import Vue from "vue";
import Api from "../api/api.js";
import config from "./config.js";
import store from "@/store/store.js";

Api.config.endpoint = config.api;

Api.config.onStart = () => {
  store.dispatch("isLoading", true);
};

Api.config.onComplete = () => {
  store.dispatch("isLoading", false);
};

Api.config.onError = error => {
  if (config.debug) {
    window.console.error(error);
  }

  // handle requests that return no auth
  if (error.code === 403) {
    store.dispatch("user/logout", true);
  }
};

// Ping API every 5 minutes to keep session alive
let ping = setInterval(Api.auth.user, 5 * 60 * 1000);

Api.config.onSuccess = () => {
  clearInterval(ping);
  ping = setInterval(Api.auth.user, 5 * 60 * 1000);
};

Vue.prototype.$api = Api;
