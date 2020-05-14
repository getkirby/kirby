<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load() {
      this.fields = {
        license: {
          label: this.$t("license.register.label"),
          type: "text",
          required: true,
          counter: false,
          placeholder: "K3-",
          help: this.$t("license.register.help")
        },
        email: {
          label: this.$t("email"),
          type: "email",
          required: true,
          counter: false
        }
      };

      this.submitButton = {
        color: "green",
        icon: "key",
        text: this.$t("license.register")
      };

      this.values = {
        license: null,
        email: null
      };
    },
    async submit() {
      await this.$api.system.register();
      return true;
    }
  }
}
</script>
