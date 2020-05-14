<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  data() {
    return {
      languages: [],
    };
  },
  async created() {
    this.languages = await this.$model.translations.options();
  },
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.users.get(id, {
        select: ["language"]
      });

      this.fields = {
        language: {
          label: this.$t("language"),
          type: "select",
          icon: "globe",
          options: this.languages,
          required: true,
          empty: false
        }
      };

      this.submitButton = this.$t("change");
    },
    async submit() {
      return await this.$api.users.changeLanguage(this.id, this.values.language);
    }
  }
}
</script>
