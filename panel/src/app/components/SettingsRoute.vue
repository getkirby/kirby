<template>
  <k-settings-view />
</template>

<script>
export default {
  created() {
    this.$model.system.title(this.$t("view.settings"));
  }
};
</script>
