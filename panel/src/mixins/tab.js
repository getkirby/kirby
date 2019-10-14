export default {
  mounted() {
    this.$el.addEventListener("keyup", this.onTab);
    this.$el.addEventListener("blur", this.onUntab);
  },
  destroyed() {
    this.$el.removeEventListener("keyup", this.onTab);
    this.$el.removeEventListener("blur", this.onUntab);
  },
  methods: {
    focus() {
      if (this.$el.focus) {
        this.$el.focus();
      }
    },
    onTab(e) {
      if (e.keyCode === 9) {
        this.$el.dataset.tabbed = true;
      }
    },
    onUntab() {
      delete this.$el.dataset.tabbed;
    },
    tab() {
      this.$el.focus();
      this.$el.dataset.tabbed = true;
    },
    untab() {
      delete this.$el.dataset.tabbed;
    },
  }
};
