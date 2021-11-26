<script>
import FormDialog from "./FormDialog.vue";

export default {
  extends: FormDialog,
  watch: {
    "model.name"(name) {
      if (this.fields.code.disabled) {
        return;
      }

      this.onNameChanges(name);
    },
    "model.code"(code) {
      if (this.fields.code.disabled) {
        return;
      }

      this.model.code = this.$helper.slug(code, [this.$system.ascii]);
      this.onCodeChanges(this.model.code);
    }
  },
  methods: {
    onCodeChanges(code) {
      if (!code) {
        return (this.model.locale = null);
      }

      if (code.length >= 2) {
        // if the locale value entered has a hyphen
        // it divides the text and capitalizes the hyphen after it
        // code: en-us > locale: en_US
        if (code.indexOf("-") !== -1) {
          let segments = code.split("-");
          let locale = [segments[0], segments[1].toUpperCase()];
          this.model.locale = locale.join("_");
        } else {
          // if the entered language code exists
          // matches the locale values in the languages defined in the system
          let locales = this.$system.locales || [];
          if (locales?.[code]) {
            this.model.locale = locales[code];
          } else {
            this.model.locale = null;
          }
        }
      }
    },
    onNameChanges(name) {
      this.model.code = this.$helper
        .slug(name, [this.model.rules, this.$system.ascii])
        .substr(0, 2);
    }
  }
};
</script>
