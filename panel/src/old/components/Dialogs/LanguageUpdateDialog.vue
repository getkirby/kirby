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
        this.language = await this.$api.get("languages/" + code);

        const keys = Object.keys(this.language.locale);
        if (keys.length === 1) {
          this.language.locale = this.language.locale[keys[0]];
        }

        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      if (this.language.name.length === 0) {
        this.$refs.dialog.error(this.$t("error.language.name"));
        return;
      }

      if (typeof this.language.locale === "string") {
        this.language.locale = this.language.locale.trim() || null;
      }

      try {
        await this.$api.patch("languages/" + this.language.code, {
          name: this.language.name,
          direction: this.language.direction,
          locale: this.language.locale
        });

        await this.$store.dispatch("languages/load");

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
