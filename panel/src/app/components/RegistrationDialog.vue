<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load() {
      this.fields = {
        license: {
          counter: false,
          help: this.$t("license.register.help"),
          label: this.$t("license.register.label"),
          placeholder: "K3-",
          required: true,
          trim: true,
          type: "text",
        },
        email: {
          counter: false,
          label: this.$t("email"),
          required: true,
          type: "email",
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
