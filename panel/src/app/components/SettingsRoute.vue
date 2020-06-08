<template>
  <k-settings-view
    :license="license"
    :version="version"
  />
</template>

<script>
export default {
  created() {
    this.$model.system.title(this.$t("view.settings"));
  },
  computed: {
    license() {
      return this.$store.state.system.license;
    },
    multilang() {
      return this.$store.state.system.multilang;
    },
    version() {
      return this.$store.state.system.version;
    }
  }
};
</script>
