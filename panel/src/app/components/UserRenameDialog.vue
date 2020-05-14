<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  watch: {
    "values.name"(value) {
      this.values.name = value.trim();
    },
  },
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.users.get(id, {
        select: ["name"]
      });

      this.fields = {
        name: {
          label: this.$t("name"),
          type: "text",
          icon: "user",
          preselect: true
        }
      };

      this.submitButton = this.$t("rename");
    },
    async submit() {
      return await this.$api.users.changeName(this.id, this.values.name);
    }
  }
}
</script>
