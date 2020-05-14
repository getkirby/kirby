import Padding from "../storybook/Padding.js";
import AsyncFormDialog from "./AsyncFormDialog.vue";

export default {
  title: "UI | Dialog / Async Form Dialog",
  decorators: [Padding]
};

const RegistrationDialog = {
  extends: AsyncFormDialog,
  methods: {
    async load() {
      await new Promise(r => setTimeout(r, 500));

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
        text: "Register"
      };

      this.values = {
        email: "bastian@getkirby.com"
      };

    },
    async submit() {
      await new Promise(r => setTimeout(r, 500));
      return true;
    }
  }
};

export const registrationDialog = () => ({
  components: {
    "k-registration-dialog": RegistrationDialog
  },
  template: `
    <div>
      <k-button @click="$refs.dialog.open()">Open</k-button>
      <k-registration-dialog ref="dialog" />
    </div>
  `
});
