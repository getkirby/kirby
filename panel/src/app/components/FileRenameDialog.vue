<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  watch: {
    "values.name"(name) {
      this.values.name = this.$helper.slug(name);
    }
  },
  methods: {
    async load(parent, filename) {
      this.filename = filename;
      this.parent   = parent;
      this.file     = await this.$api.files.get(parent, filename, {
        select: ["id", "filename", "name", "extension"]
      });

      this.fields = {
        name: {
          label: this.$t("name"),
          type: "text",
          required: true,
          icon: "title",
          after: "." + this.file.extension,
          preselect: true
        }
      };

      this.submitButton = this.$t("rename");

      this.values = {
        name: this.file.name
      };
    },
    async submit() {
      return await this.$api.files.changeName(this.parent, this.filename, this.file.name);
    },
    async validate() {
      if (this.values.name.length === 0) {
        throw this.$t("error.file.changeName.empty");
      }
    }
  }
}
</script>
