export default {
  mounted() {
    this.$el.addEventListener("keyup", this.onTab, true);
    this.$el.addEventListener("blur", this.onUntab, true);
  },
  destroyed() {
    this.$el.removeEventListener("keyup", this.onTab, true);
    this.$el.removeEventListener("blur", this.onUntab, true);
  },
  methods: {
    focus() {
      if (this.$el.focus) {
        this.$el.focus();
      }
    },
    onTab(e) {
      if (e.keyCode === 9) {
        this.$el.setAttribute("data-tabbed", true);
      }
    },
    onUntab() {
      this.$el.removeAttribute("data-tabbed");
    },
    tab() {
      this.$el.focus();
      this.$el.setAttribute("data-tabbed", true);
    },
    untab() {
      this.$el.removeAttribute("data-tabbed");
    }
  }
};
