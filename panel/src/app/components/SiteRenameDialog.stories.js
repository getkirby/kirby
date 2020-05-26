import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Dialogs / Site Rename Dialog",
  decorators: [Padding],
};

export const regular = () => ({
  methods: {
    onSuccess: action("success"),
    open() {
      this.$refs.dialog.open();
    },
  },
  template: `
    <div>
      <k-button icon="title" @click="open">Change Site Title</k-button>
      <k-site-rename-dialog ref="dialog" @success="onSuccess" />
    </div>
  `
});
