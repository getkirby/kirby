import Vue from "vue";
import Api from "@getkirby/api-js";
import config from "./config.js";
import store from "@/store/store.js";

import files from "@/api/files.js";
import pages from "@/api/pages.js";
import roles from "@/api/roles.js";
import site from "@/api/site.js";
import users from "@/api/users.js";

Api.config.endpoint = config.api;
Api.config.csrf     = window.panel.csrf;
Api.requests = [];

Api.config.onRequest = (options) => {
  if (store.state.languages.current) {
    options.headers["x-language"] = store.state.languages.current.code;
  }
  return options;
};

Api.config.onStart = (requestId) => {
  store.dispatch("isLoading", true);
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

// Add custom endpoint methods
Api.files = Object.assign(Api.files, files);
Api.pages = Object.assign(Api.pages, pages);
Api.roles = Object.assign(Api.roles, roles);
Api.site  = Object.assign(Api.site, site);
Api.users = Object.assign(Api.users, users);

Vue.prototype.$api = Api;
