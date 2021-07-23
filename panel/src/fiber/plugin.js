
import Fiber from "./index";
import dialog from "./dialog"
import dropdown from "./dropdown"

export default {
  install(app) {
    app.prototype.$dialog = window.panel.$dialog = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;

    app.prototype.$go = window.panel.$go = function (path, options) {
      return Fiber.go(this.$url(path), options);
    };

    app.prototype.$reload = window.panel.$reload = function (options) {
      return Fiber.reload(options);
    };

    app.prototype.$request = async function (...args) {
      return await Fiber.request(...args);
    };

    app.prototype.$url = function (...args) {
      return Fiber.url(...args);
    };
  }
};