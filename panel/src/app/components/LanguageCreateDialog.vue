<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  watch: {
    "values.name"(name) {
      this.values.code = name.substr(0, 2);
    },
  },
  methods: {
    fieldSetup() {
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
          slug: true,
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
          trim: true,
          type: "text",
          placeholder: "en_US"
        },
      };
    },
    async load() {
      this.values = {
        code: "",
        direction: "ltr",
        locale: "",
        name: ""
      };
      this.fields = this.fieldSetup();
      this.submitButton = this.$t("language.create");
    },
    async submit() {
      await this.$model.languages.create(this.values);
    },
    async validate() {
      if (this.values.name.length === 0) {
        throw this.$t("error.language.name");
      }
    }
  }
}
</script>
