<template>
  <k-form-dialog
    ref="dialog"
    v-model="language"
    :fields="fields"
    size="medium"
    @submit="submit"
  />
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
    async open(code) {
      try {
        this.language = await this.$api.languages.get(code);

        const localeKeys = Object.keys(this.language.locale);
        if (localeKeys.length === 1) {
          this.language.locale = this.language.locale[localeKeys[0]];
        }

        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      if (this.language.name.length === 0) {
        return this.$refs.dialog.error(this.$t("error.language.name"));
      }

      if (typeof this.language.locale === "string") {
        this.language.locale = this.language.locale.trim() || null;
      }

      try {
        await this.$api.languages.update(this.language.code, {
          name: this.language.name,
          direction: this.language.direction,
          locale: this.language.locale
        });

        this.success({
          message: this.$t("language.updated"),
          event: "language.update"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
