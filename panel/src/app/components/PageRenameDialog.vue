<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  data() {
    return {
      fields: {
        title: {
          icon: "title",
          label: this.$t("title"),
          preselect: true,
          required: true,
          trim: true,
          type: "text",
        }
      },
      submitButton: this.$t("rename")
    };
  },
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.pages.get(id, {
        select: ["title"]
      });
    },
    async submit() {
      return await this.$model.pages.changeTitle(this.id, this.values.title);
      //TODO: routing to new path in page view
    },
    async validate() {
      if (this.values.title.length === 0) {
        throw this.$t("error.page.changeTitle.empty");
      }
    }
  }
}
</script>
