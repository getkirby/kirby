<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(parent, filename) {
      const file = await this.$api.files.get(parent, filename, {
        select: ["name", "extension"]
      });

      this.filename = filename;
      this.parent   = parent;

      this.fields = {
        name: {
          label: this.$t("name"),
          type: "text",
          required: true,
          icon: "title",
          after: "." + file.extension,
          preselect: true,
          slug: "@._-"
        }
      };

      this.submitButton = this.$t("rename");

      this.values = {
        name: file.name
      };
    },
    async submit() {
      return await this.$model.files.changeName(
        this.parent,
        this.filename,
        this.values.name
      );
    },
    async validate() {
      if (this.values.name.length === 0) {
        throw this.$t("error.file.changeName.empty");
      }
    }
  }
}
</script>
