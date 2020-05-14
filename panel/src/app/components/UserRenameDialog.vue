<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.users.get(id, {
        select: ["name"]
      });

      this.fields = {
        name: {
          icon: "user",
          label: this.$t("name"),
          preselect: true,
          trim: true,
          type: "text",
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
