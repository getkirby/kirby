import Fiber from "./index";
import dialog from "./dialog";
import dropdown from "./dropdown";
import search from "./search";

export default {
  install(app) {
    app.prototype.$fiber = window.panel.$fiber = Fiber;
    app.prototype.$dialog = window.panel.$dialog = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;
    app.prototype.$go = window.panel.$go = Fiber.go.bind(Fiber);
    app.prototype.$reload = window.panel.$reload = Fiber.reload.bind(Fiber);
    app.prototype.$request = window.panel.$request = Fiber.request.bind(Fiber);
    app.prototype.$search = window.panel.$search = search;
    app.prototype.$url = window.panel.$url = Fiber.url.bind(Fiber);
  }
};
