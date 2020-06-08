<template>
  <k-settings-view v-bind="view" @update="onUpdate" />
</template>

<script>
export default {
  computed: {
    view() {
      return {
        license: this.$store.state.system.license,
        update:  this.$store.state.system.update,
        version: this.$store.state.system.version,
      }
    }
  },
  created() {
    this.$model.system.title(this.$t("view.settings"));
  },
  methods: {
    async onUpdate() {
      const update = await this.$model.system.update();

      if (update.status === "ok") {
        this.$store.dispatch("notification/success", this.$t("update.status.ok"));
      }
    }
  }
};
</script>
