<template>
  <k-remove-dialog
    ref="dialog"
    :text="$t('language.delete.confirm', { name: language.name })"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      language: {
        name: null
      }
    };
  },
  methods: {
    async open(code) {
      try {
        this.language = await this.$api.languages.get(code);
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.languages.delete(this.language.code);

        this.success({
          message: this.$t("language.deleted"),
          event: "language.delete"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
