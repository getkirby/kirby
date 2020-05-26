<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      this.id = id;
      this.values = await this.$api.users.get(id, {
        select: ["email"]
      });

      this.fields = {
        email: {
          label: this.$t("email"),
          preselect: true,
          required: true,
          type: "email",
        }
      };

      this.submitButton = this.$t("change");
    },
    async submit() {
      return await this.$model.users.changeEmail(this.id, this.values.email);
      // TODO: routing in view
    }
  }
}
</script>
