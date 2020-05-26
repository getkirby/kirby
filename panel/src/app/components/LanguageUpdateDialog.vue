<script>
import LanguageCreateDialog from "./LanguageCreateDialog.vue";

export default {
  extends: LanguageCreateDialog,
  watch: {
    "values.name"(name) {
      return false;
    },
  },
  methods: {
    async load(code) {
      this.code   = code;
      this.values = await this.$api.languages.get(code);
      this.fields = this.fieldSetup();

      // the code of an existing language cannot be changed
      this.fields.code.disabled = true;

      // change the locale field if the locale is defined as object
      if (typeof this.values.locale === "object") {
        this.fields.locale = {
          label: this.fields.locale.label,
          type: "info",
          text: this.$t("language.locale.warning")
        };
      }

      this.submitButton = this.$t("save");
    },
    async submit() {
      return await this.$model.languages.update(this.code, this.values);
    }
  }
}
</script>
