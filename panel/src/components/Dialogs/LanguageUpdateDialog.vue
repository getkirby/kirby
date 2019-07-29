<template>
  <k-dialog
    ref="dialog"
    :button="$t('save')"
    :notification="notification"
    size="medium"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="language"
      @submit="submit"
    />
  </k-dialog>
</template>

<script>
import LanguageCreateDialog from "./LanguageCreateDialog.vue";

export default {
  mixins: [LanguageCreateDialog],
  computed: {
    fields() {
      let fields = LanguageCreateDialog.computed.fields.apply(this);
      fields.code.disabled = true;

      if (typeof this.language.locale === "object") {
        fields.locale = {
          label: fields.locale.label,
          type: "info",
          text: this.$t("language.locale.warning")
        };
      }

      return fields;
    }
  },
  methods: {
    onNameChanges() {
      return false;
    },
    open(code) {
      this.$api
        .get("languages/" + code)
        .then(language => {
          this.language = language;

          const localeKeys = Object.keys(this.language.locale);

          if (localeKeys.length === 1) {
            this.language.locale = this.language.locale[localeKeys[0]];
          }

          this.$refs.dialog.open();
        })
        .catch (error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api
        .patch("languages/" + this.language.code, {
          name: this.language.name,
          direction: this.language.direction,
          locale: this.language.locale
        })
        .then(() => {
          this.$store.dispatch("languages/load");
          this.success({
            message: this.$t("language.updated"),
            event: "language.update"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
