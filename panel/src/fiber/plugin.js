
import Fiber from "./index";
import dialog from "./dialog"
import dropdown from "./dropdown"
import search from "./search"

import store from "../store/store.js";

export default {
  install(app) {
    app.prototype.$dialog   = window.panel.$dialog   = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;
    app.prototype.$go       = window.panel.$go       = (path, options) => Fiber.go(Fiber.url(path), options);
    app.prototype.$reload   = window.panel.$reload   = (options) => Fiber.reload(options);
    app.prototype.$request  = window.panel.$request  = (...args) => Fiber.request(...args);
    app.prototype.$search   = window.panel.$search   = search;
    app.prototype.$url      = window.panel.$url      = (...args) => Fiber.url(...args);

    // Connect Fiber events to Vuex store loading state
    document.addEventListener("fiber.start", (e) => {
      if (e.detail.silent !== true) {
        store.dispatch("isLoading", true);
      }
    });

    document.addEventListener("fiber.finish", () => {
      if (app.$api.requests.length === 0) {
        store.dispatch("isLoading", false);
      }
    });
  }
};