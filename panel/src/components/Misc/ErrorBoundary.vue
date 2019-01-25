<script>
import config from "@/config/config.js";

export default {
  data() {
    return {
      error: null
    };
  },
  errorCaptured(error) {
    if (config.debug) {
      window.console.error(error);
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
