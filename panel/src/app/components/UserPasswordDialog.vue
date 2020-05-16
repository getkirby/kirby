<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      // test if the user exists
      await this.$api.users.get(id);

      this.id = id;

      // always start with empty fields
      this.values = {
        password: null,
        passwordConfirmation: null,
      };

      this.fields = {
        password: {
          label: this.$t("user.changePassword.new"),
          required: true,
          minlength: 8,
          type: "password"
        },
        passwordConfirmation: {
          label: this.$t("user.changePassword.new.confirm"),
          required: true,
          minlength: 8,
          type: "password",
        }
      };

      this.submitButton = this.$t("change");
    },
    async submit() {
      return await this.$model.users.changePassword(
        this.id,
        this.values.password
      );
    },
    async validate() {
      if (!this.values.password || this.values.password.length < 8) {
        throw this.$t("error.user.password.invalid");
      }

      if (this.values.password !== this.values.passwordConfirmation) {
        throw this.$t("error.user.password.notSame");
      }
    }
  }
}
</script>
