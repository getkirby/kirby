<template>
  <k-remove-dialog
    ref="dialog"
    :text="$t('file.delete.confirm', { filename: filename })"
    @submit="submit"
  />
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
    async open(parent, filename) {
      try {
        const file = await this.$api.files.get(parent, filename);
        this.id       = file.id;
        this.filename = file.filename;
        this.parent   = parent;
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.files.delete(this.parent, this.filename);

        // remove data from cache
        this.$store.dispatch("content/remove", "files/" + this.id);

        this.$store.dispatch("notification/success", ":)");
        this.$events.$emit("file.delete", this.id);
        this.$emit("success");
        this.$refs.dialog.close();

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
