import Vue from "vue";
import Api from "../api/api.js";
import config from "./config.js";
import store from "@/store/store.js";

Api.config.endpoint = config.api;
Api.requests = [];

Api.config.onStart = (requestId, silent) => {
  if (silent === false) {
    store.dispatch("isLoading", true);
  }
  Api.requests.push(requestId);
};

Api.config.onComplete = (requestId) => {
  Api.requests = Api.requests.filter(value => {
    return value !== requestId;
  });

  if (Api.requests.length === 0) {
    store.dispatch("isLoading", false);
  }
};

Api.config.onError = error => {
  if (config.debug) {
    window.console.error(error);
  }

  // handle requests that return no auth
  if (
    error.code === 403 &&
    (error.message === "Unauthenticated" || error.key === "access.panel")
  ) {
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
