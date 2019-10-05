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
      id: null,
      parent: null,
      filename: null
    };
  },
  methods: {
    open(parent, filename) {
      this.$api.files.get(parent, filename)
        .then(file => {
          this.id       = file.id;
          this.filename = file.filename;
          this.parent   = parent;
          this.$refs.dialog.open();
        })
        .catch (error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.files
        .delete(this.parent, this.filename)
        .then(() => {
          // remove data from cache
          this.$store.dispatch("content/remove", "files/" + this.id);
          this.$store.dispatch("notification/success", ":)");
          this.$events.$emit("file.delete", this.id);
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
