import mitt from 'mitt';

export default {
  install(app) {

    const emitter = mitt();

    const bus = {
      entered: null,
      $on: emitter.on,
      $off: emitter.off,
      $emit: emitter.emit,
    };

    bus.click = (e) => bus.$emit("click", e);

    bus.drop = (e) => {
      bus.prevent(e);
      bus.$emit("drop", e);
    };

    bus.dragenter = (e) => {
      bus.entered = e.target;
      bus.prevent(e);
      bus.$emit("dragenter", e);
    };

    bus.dragleave = (e) => {
      bus.prevent(e);

      if (bus.entered === e.target) {
        bus.$emit("dragleave", e);
      }
    };

    bus.keydown = (e) => {

      let parts = ['keydown'];

      // with meta or control key
      if (e.metaKey || e.ctrlKey) {
        parts.push('cmd');
      }

      if (e.altKey === true) {
        parts.push("alt");
      }

      if (e.shiftKey === true) {
        parts.push('shift');
      }

      let key = app.prototype.$helper.string.lcfirst(e.key);

      // key replacements
      const keys = {
        "escape": "esc",
        "arrowUp": "up",
        "arrowDown": "down",
        "arrowLeft": "left",
        "arrowRight": "right"
      };

      if (keys[key]) {
        key = keys[key];
      }

      if (["alt", "control", "shift", "meta"].includes(key) === false) {
        parts.push(key);
      }

      bus.$emit(parts.join("."), e);
      bus.$emit("keydown", e);
    };

    bus.keyup = (e) => bus.$emit("keyup", e);
    bus.online = (e) => bus.$emit("online", e);
    bus.offline = (e) => bus.$emit("offline", e);

    bus.prevent = (e) => {
      e.stopPropagation();
      e.preventDefault();
    };

    window.addEventListener("online", bus.online);
    window.addEventListener("offline", bus.offline);
    window.addEventListener("dragenter", bus.dragenter, false);
    window.addEventListener("dragover", bus.prevent, false);
    window.addEventListener("dragexit", bus.prevent, false);
    window.addEventListener("dragleave", bus.dragleave, false);
    window.addEventListener("drop", bus.drop, false);
    window.addEventListener("keydown", bus.keydown, false);
    window.addEventListener("keyup", bus.keyup, false);
    document.addEventListener("click", bus.click, false);

    app.prototype.$events = bus;
  }
};
