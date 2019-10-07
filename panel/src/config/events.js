
export default {
  install(Vue) {
    Vue.prototype.$events = new Vue({
      data() {
        return {
          entered: null
        };
      },
      created() {
        window.addEventListener("online", this.online);
        window.addEventListener("offline", this.offline);
        window.addEventListener("dragenter", this.dragenter, false);
        window.addEventListener("dragover", this.prevent, false);
        window.addEventListener("dragexit", this.prevent, false);
        window.addEventListener("dragleave", this.dragleave, false);
        window.addEventListener("drop", this.drop, false);
        window.addEventListener("keydown", this.keydown, false);
        window.addEventListener("keyup", this.keyup, false);
        document.addEventListener("click", this.click, false);
      },
      destroyed() {
        window.removeEventListener("online", this.online);
        window.removeEventListener("offline", this.offline);
        window.removeEventListener("dragenter", this.dragenter, false);
        window.removeEventListener("dragover", this.prevent, false);
        window.removeEventListener("dragexit", this.prevent, false);
        window.removeEventListener("dragleave", this.dragleave, false);
        window.removeEventListener("drop", this.drop, false);
        window.removeEventListener("keydown", this.keydown, false);
        window.removeEventListener("keyup", this.keyup, false);
        document.removeEventListener("click", this.click, false);
      },
      methods: {
        click(e) {
          this.$emit("click", e);
        },
        drop(e) {
          this.prevent(e);
          this.$emit("drop", e);
        },
        dragenter(e) {
          this.entered = e.target;
          this.prevent(e);
          this.$emit("dragenter", e);
        },
        dragleave(e) {
          this.prevent(e);
          if (this.entered === e.target) {
            this.$emit("dragleave", e);
          }
        },
        keydown(e) {

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

          let key = this.$helper.string.lcfirst(e.key);

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

          this.$emit(parts.join("."), e);
          this.$emit("keydown", e);
        },
        keyup(e) {
          this.$emit("keyup", e);
        },
        online(e) {
          this.$emit("online", e);
        },
        offline(e) {
          this.$emit("offline", e);
        },
        prevent(e) {
          e.stopPropagation();
          e.preventDefault();
        },
      }
    });
  }
};
