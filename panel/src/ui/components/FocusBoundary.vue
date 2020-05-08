<template>
  <div v-on="$listeners">
    <slot />
  </div>
</template>

<script>
export default {
  mounted() {
    document.addEventListener("focus", this.focustrap, true);
  },
  destroyed() {
    document.removeEventListener("focus", this.focustrap);
  },
  methods: {
    focus() {
      let target = this.$el.querySelector(
        "[autofocus], [data-autofocus], input, textarea, select, button"
      );

      if (target && typeof target.focus === "function") {
        target.focus();
        return;
      }
    },
    focustrap(e) {
      if (this.$el && this.$el.contains(e.target) === false) {
        this.focus();
      }
    }
  }
};
</script>
