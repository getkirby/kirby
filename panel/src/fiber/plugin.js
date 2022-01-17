import Fiber from "./index";
import dialog from "./dialog";
import dropdown from "./dropdown";
import search from "./search";

export default {
  install(app) {
    const fiber = new Fiber();

    app.prototype.$fiber = window.panel.$fiber = fiber;
    app.prototype.$dialog = window.panel.$dialog = dialog;
    app.prototype.$dropdown = window.panel.$dropdown = dropdown;
    app.prototype.$go = window.panel.$go = fiber.go.bind(fiber);
    app.prototype.$reload = window.panel.$reload = fiber.reload.bind(fiber);
    app.prototype.$request = window.panel.$request = fiber.request.bind(fiber);
    app.prototype.$search = window.panel.$search = search;
    app.prototype.$url = window.panel.$url = fiber.url.bind(fiber);
  }
};
