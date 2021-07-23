
import Fiber from "./index";
import dialog from "./dialog"
import dropdown from "./dropdown"

export default {
  install(app) {
    app.prototype.$dialog   = window.panel.$dialog   = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;
    app.prototype.$go       = window.panel.$go       = (path, options) => Fiber.go(Fiber.url(path), options);
    app.prototype.$reload   = window.panel.$reload   = Fiber.reload;
    app.prototype.$request  = window.panel.$request  = Fiber.request;
    app.prototype.$url      = window.panel.$url      = (...args) => Fiber.url(...args);
  }
};