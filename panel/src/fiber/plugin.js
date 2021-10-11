import Fiber from "./index";
import dialog from "./dialog";
import dropdown from "./dropdown";
import search from "./search";

export default {
  install(app) {
    app.prototype.$dialog = window.panel.$dialog = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;
    app.prototype.$go = window.panel.$go = (path, options) =>
      Fiber.go(Fiber.url(path), options);
    app.prototype.$reload = window.panel.$reload = (options) =>
      Fiber.reload(options);
    app.prototype.$request = window.panel.$request = (...args) =>
      Fiber.request(...args);
    app.prototype.$search = window.panel.$search = search;
    app.prototype.$url = window.panel.$url = (...args) => Fiber.url(...args);
  }
};
