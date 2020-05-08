import Padding from "../storybook/Padding.js";
import AsyncFormDialog from "./AsyncFormDialog.vue";

export default {
  title: "Lab | Async Form Dialog",
  decorators: [Padding]
};

const RegistrationDialog = {
  extends: AsyncFormDialog,
  computed: {
    fields() {
      return {
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
    },
    submitButton() {
      return "Register";
    }
  },
  methods: {
    async load() {
      await new Promise(r => setTimeout(r, 500));

      return {
        email: "mail@bastianallgeier.com"
      };
    },
    async submit(values) {
      await new Promise(r => setTimeout(r, 500));

      throw "The registration failed";
    },
    async validate(values) {
      if (values.license.length !== 5) {
        throw "Please enter a valid email";
      }
    }
  }
};

export const simple = () => ({
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
