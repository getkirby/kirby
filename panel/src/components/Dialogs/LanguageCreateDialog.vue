<template>
  <k-dialog
    ref="dialog"
    :button="$t('language.create')"
    :notification="notification"
    theme="positive"
    size="medium"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      :novalidate="true"
      v-model="language"
      @submit="submit"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
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
          type: "text",
          placeholder: "en_US"
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
    }
  },
  methods: {
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
    submit() {
      this.$api
        .post("languages", {
          name: this.language.name,
          code: this.language.code,
          direction: this.language.direction,
          locale: this.language.locale
        })
        .then(() => {
          this.$store.dispatch("languages/load");
          this.success({
            message: ":)",
            event: "language.create"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
