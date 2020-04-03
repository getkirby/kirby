import { action } from "@storybook/addon-actions";
import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Dialog",
  decorators: [withKnobs]
};

export const configurator = () => ({
  props: {
    cancelButton: {
      default: text('cancelButton', 'Cancel')
    },
    icon: {
      default: text('icon', 'check')
    },
    size: {
      default: select('size', ['small', 'default', 'medium', 'large'], 'default')
    },
    submitButton: {
      default: text('submitButton', 'Ok')
    },
    text: {
      default: text('text', 'This is a nice dialog')
    },
    theme: {
      default: select('theme', ['', 'positive', 'negative'], '')
    },
  },
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
        v-bind="$props"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      >
        {{ text }}
      </k-dialog>
    </div>
  `,
});
