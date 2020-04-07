import Dialog from "./Dialog.vue";
import { action } from "@storybook/addon-actions";
import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Dialog / Dialog",
  decorators: [withKnobs],
  component: Dialog
};

export const regular = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    open: action("open"),
    submit: action("submit")
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>

      <k-dialog
        ref="dialog"
        text="This is a nice dialog"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

