<template>
  <k-remove-dialog
    ref="dialog"
    :text="$t('language.delete.confirm', { name: language.name })"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/ui/mixins/dialog.js";

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
    open(code) {
      this.$api.get("languages/" + code)
        .then(language => {
          this.language = language;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.delete("languages/" + this.language.code)
        .then(() => {
          this.$store.dispatch("languages/load");
          this.success({
            message: this.$t("language.deleted"),
            event: "language.delete"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
