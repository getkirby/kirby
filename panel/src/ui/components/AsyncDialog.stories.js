import Padding from "../storybook/Padding.js";
import AsyncDialog from "./AsyncDialog.vue";

export default {
  title: "UI | Dialog / Async Dialog",
  decorators: [Padding]
};

const PageDeleteDialog = {
  extends: AsyncDialog,
  methods: {
    async load() {
      await new Promise(r => setTimeout(r, 500));

      this.submitButton = {
        icon: "trash",
        color: "red",
        text: "Delete"
      };

      this.text = "Do you really want to delete this page?";
    },
    async submit() {
      await new Promise(r => setTimeout(r, 500));
    }
  }
};

export const pageDeleteDialog = () => ({
  components: {
    "k-page-delete-dialog": PageDeleteDialog
  },
  template: `
    <div>
      <k-button @click="$refs.dialog.open()">Open</k-button>
      <k-page-delete-dialog ref="dialog" />
    </div>
  `
});
