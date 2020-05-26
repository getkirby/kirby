import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import LanguageCreateDialog from "./LanguageCreateDialog.vue";

export default {
  title: "App | Dialogs / Language Create Dialog",
  component: LanguageCreateDialog,
  decorators: [Padding],
};

export const regular = () => ({
  methods: {
    open() {
      this.$refs.dialog.open();
    }
  },
  template: `
    <div>
      <k-button @click="open">Open</k-button>
      <k-language-create-dialog ref="dialog" />
    </div>
  `
});

