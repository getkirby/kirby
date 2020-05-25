import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import FileRemoveDialog from "./FileRemoveDialog.vue";

export default {
  title: "App | Dialogs / File Remove Dialog",
  component: FileRemoveDialog,
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
      <k-file-remove-dialog ref="dialog" />
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
      <k-file-remove-dialog ref="dialog" />
    </div>
  `,
});


