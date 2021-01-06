<template>
  <k-form-dialog
    ref="dialog"
    v-model="language"
    :fields="fields"
    :submit-button="$t('language.create')"
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
        name: "",
        code: "",
        direction: "ltr",
        locale: ""
      }
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: this.$t("language.name"),
          type: "text",
          required: true,
          icon: "title",
        },
        code: {
          label: this.$t("language.code"),
          type: "text",
          required: true,
          counter: false,
          icon: "globe",
          width: "1/2"
        },
        direction: {
          label: this.$t("language.direction"),
          type: "select",
          required: true,
          empty: false,
          options: [
            { value: "ltr", text: this.$t("language.direction.ltr") },
            { value: "rtl", text: this.$t("language.direction.rtl") }
          ],
          width: "1/2"
        },
        locale: {
          label: this.$t("language.locale"),
          type: "text"
        },
      };
    },
    system() {
      return this.$store.state.system.info;
    }
  },
  watch: {
    "language.name"(name) {
      this.onNameChanges(name);
    },
    "language.code"(code) {
      this.language.code = this.$helper.slug(code, [this.system.ascii]);
      this.onCodeChanges(this.language.code);
    }
  },
  methods: {
    onCodeChanges(code) {
      if (!code) {
        return this.language.locale = null;
      }

      if (code.length >= 2) {
        // if the locale value entered has a hyphen
        // it divides the text and capitalizes the hyphen after it
        // code: en-us > locale: en_US
        if (code.indexOf("-") !== -1) {
          let segments = code.split("-");
          let locale = [segments[0], segments[1].toUpperCase()];
          this.language.locale = locale.join("_");
        } else {
          // if the entered language code exists
          // matches the locale values in the languages defined in the system
          let locales = this.$store.state.system.info.locales || [];
          if (locales && locales[code]) {
            this.language.locale = locales[code];
          } else {
            this.language.locale = null;
          }
        }
      }
    },
    onNameChanges(name) {
      this.language.code = this.$helper.slug(name, [this.language.rules, this.system.ascii]).substr(0, 2);
    },
    open() {
      this.language = {
        name: "",
        code: "",
        direction: "ltr"
      };

      this.$refs.dialog.open();
    },
    async submit() {
      if (this.language.locale) {
        this.language.locale = this.language.locale.trim() || null;
      }

      try {
        await this.$api.languages.create({
          name: this.language.name,
          code: this.language.code,
          direction: this.language.direction,
          locale: this.language.locale
        });

        this.$store.dispatch("languages/load");
        this.success({
          message: ":)",
          event: "language.create"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
