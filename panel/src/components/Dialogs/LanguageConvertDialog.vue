<template>
  <k-dialog
    ref="dialog"
    :button="$t('change')"
    theme="negative"
    icon="check"
    @submit="submit"
  >
    <k-text v-html="$t('language.convert.confirm', { name: language.name })" />
  </k-dialog>
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
      this.$api
        .patch("languages/" + this.language.code, { default: true })
        .then(() => {
          this.fetch();
          this.$store.dispatch("languages/load");
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
