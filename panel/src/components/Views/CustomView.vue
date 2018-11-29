<template>
  <component :is="'k-' + plugin + '-plugin-view'" />
</template>

<script>
export default {
  props: {
    plugin: String
  },
  created() {
    this.$store.dispatch("view", this.plugin);
  }
};
</script>
