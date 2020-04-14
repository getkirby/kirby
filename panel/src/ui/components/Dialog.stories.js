import Dialog from "./Dialog.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Dialog / Base Dialog",
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

      <k-dialog
        ref="dialog"
        :visible="true"
        text="This is a nice dialog"
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
      <k-dialog
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
      <k-dialog
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



