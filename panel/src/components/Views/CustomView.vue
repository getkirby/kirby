<template>
  <k-error-boundary :key="plugin">
    <component :is="'k-' + plugin + '-plugin-view'" />
    <k-error-view slot="error" slot-scope="{ error }">
      {{ error.message || error }}
    </k-error-view>
  </k-error-boundary>
</template>

<script>
export default {
  props: {
    plugin: String
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.$store.dispatch("breadcrumb", []);
      vm.$store.dispatch("content/current", null);
    })
  },
  watch: {
    plugin: {
      handler() {
        this.$store.dispatch("view", this.plugin);
      },
      immediate: true
    }
  }
};
</script>
