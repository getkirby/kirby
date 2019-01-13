<template>
  <k-error-boundary :key="plugin">
    <component :is="'k-' + plugin + '-plugin-view'" />
    <k-error-view slot="error" slot-scope="{ error }">
      {{ error }}
    </k-error-view>
  </k-error-boundary>
</template>

<script>
export default {
  props: {
    plugin: String
  },
  watch: {
    plugin() {
      this.$store.dispatch("view", this.plugin);
    }
  },
  created() {
    this.$store.dispatch("view", this.plugin);
  }
};
</script>
