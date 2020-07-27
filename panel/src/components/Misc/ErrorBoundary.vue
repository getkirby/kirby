<script>
export default {
  data() {
    return {
      error: null
    };
  },
  errorCaptured(error) {
    if (this.$config.debug) {
      window.console.warn(error);
    }

    this.error = error;
    return false;
  },
  render(h) {
    if (this.error) {
      if (this.$slots.error) {
        return this.$slots.error[0];
      }

      if (this.$scopedSlots.error) {
        return this.$scopedSlots.error({
          error: this.error
        });
      }

      return h(
        "k-box",
        { attrs: { theme: "negative" } },
        this.error.message || this.error
      );
    } else {
      return this.$slots.default[0];
    }
  }
};
</script>
