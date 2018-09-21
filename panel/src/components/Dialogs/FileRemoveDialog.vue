<template>
  <k-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    @submit="submit"
  >
    <k-text v-html="$t('file.delete.confirm', { filename: filename })" />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      parent: null,
      filename: null
    };
  },
  methods: {
    open(parent, filename) {
      this.$api.files.get(parent, filename)
        .then(file => {
          this.parent = file.parent;
          this.filename = file.filename;
          this.$refs.dialog.open();
        })
        .catch (error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.files
        .delete(this.parent.id, this.filename)
        .then(() => {
          // remove data from cache
          this.$store.dispatch("form/reset", this.$route.path);

          this.$store.dispatch("notification/success", this.$t("file.deleted"));
          this.$events.$emit("file.delete");
          this.$emit("success");
          this.$refs.dialog.close();
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
