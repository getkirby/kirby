import RemoveDialog from "./RemoveDialog.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dialog / Remove Dialog",
  component: RemoveDialog,
  decorators: [Padding]
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

      <k-remove-dialog
        ref="dialog"
        text="Do you really want to delete this item?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const open = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    open: action("open"),
    submit: action("submit")
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>

      <k-remove-dialog
        ref="dialog"
        :visible="true"
        text="Do you really want to delete this item?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const errorNotification = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    open: action("open"),
    submit() {
      action("submit");
      this.$refs.dialog.error("Something went wrong");
    }
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-remove-dialog
        ref="dialog"
        text="Click confirm to raise an error"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const successNotification = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    open: action("open"),
    submit() {
      action("submit");
      this.$refs.dialog.success("Yayayay!");
    }
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-remove-dialog
        ref="dialog"
        text="Click confirm to raise a success message"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});



