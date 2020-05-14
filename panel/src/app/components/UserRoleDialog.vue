<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      this.id     = id;
      this.values = await this.$api.users.get(id, {
        select: ["role"]
      });

      // load all available roles
      this.roles = await this.$model.roles.options({ canBe: "changed" });

      // don't let non-admins promote anyone to admin
      if (this.$user.role.name !== "admin") {
        this.roles = this.roles.filter(role => {
          return role.value !== "admin";
        });
      }

      this.fields = {
        role: {
          label: this.$t("user.changeRole.select"),
          type: "radio",
          required: true,
          options: this.roles
        }
      };

      this.submitButton = this.$t("change");
    },
    async submit() {
      return await this.$api.users.changeRole(this.id, this.values.role);
    }
  }
}
</script>
