import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import FileRenameDialog from "./FileRenameDialog.vue";

export default {
  title: "App | Dialogs / File Rename Dialog",
  component: FileRenameDialog,
  decorators: [Padding],
};

export const regular = () => ({
  methods: {
    open() {
      this.$refs.dialog.open("pages/photography+animals", "free-wheely.jpg");
    }
  },
  template: `
    <div>
      <k-button @click="open">Open</k-button>
      <k-file-rename-dialog ref="dialog" />
    </div>
  `
});

export const invalidFile = () => ({
  methods: {
    open() {
      this.$refs.dialog.open("pages/photography+animals", "does-not-exist.jpg");
    },
  },
  template: `
    <div>
      <k-button @click="open">Open</k-button>
      <k-file-rename-dialog ref="dialog" />
    </div>
  `,
});


