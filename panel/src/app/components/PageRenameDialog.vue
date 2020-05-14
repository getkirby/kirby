<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  data() {
    return {
      fields: {
        title: {
          label: this.$t("title"),
          type: "text",
          required: true,
          icon: "title",
          preselect: true
        }
      },
      submitButton: this.$t("rename")
    };
  },
  watch: {
    "values.title"(value) {
      this.values.title = value.trim();
    }
  },
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.pages.get(id, {
        select: ["title"]
      });
    },
    async submit() {
      return await this.$api.pages.changeTitle(this.id, this.values.title);
    },
    async validate() {
      if (this.values.title.length === 0) {
        throw this.$t("error.page.changeTitle.empty");
      }
    }
  }
}
</script>
