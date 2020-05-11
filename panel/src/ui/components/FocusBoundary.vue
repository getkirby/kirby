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
    focustrap(e) {
      if (this.$el && this.$el.contains(e.target) === false) {
        this.focus();
      }
    }
  }
};
</script>
