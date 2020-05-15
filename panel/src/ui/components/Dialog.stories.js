import Dialog from "./Dialog.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dialog / Dialog",
  component: Dialog,
  decorators: [Padding],
};

export const regular = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    open: action("open"),
    submit() {
      action("submit")();
      alert("submitted");
      this.$refs.dialog.close();
    }
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

export const customSubmitButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        :submitButton="{
          icon: 'trash',
          text: 'Delete',
          color: 'red'
        }"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const legacySubmitButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        button="Delete"
        icon="trash"
        theme="negative"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const noSubmitButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        :submit-button="false"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const customCancelButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        :cancelButton="{
          icon: 'alert',
          text: 'Nope',
          color: 'purple'
        }"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const legacyCancelButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        cancel="Nope"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const noCancelButton = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-dialog
        ref="dialog"
        :cancel-button="false"
        text="Delete this?"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const open = () => ({
  extends: regular(),
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

export const loading = () => ({
  extends: regular(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>

      <k-dialog
        ref="dialog"
        :loading="true"
        :visible="true"
        text="This is a dialog while loading"
        @cancel="cancel"
        @close="close"
        @open="open"
        @submit="submit"
      />
    </div>
  `,
});

export const errorNotification = () => ({
  extends: regular(),
  methods: {
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
  extends: regular(),
  methods: {
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
