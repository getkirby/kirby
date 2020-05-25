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
    onCancel: action("cancel"),
    onClose: action("close"),
    onOpen: action("open"),
    onSubmit() {
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
      />
    </div>
  `,
});

export const preloading = () => ({
  extends: regular(),
  methods: {
    open() {
      this.$refs.dialog.preload();
      setTimeout(() => {
        this.$refs.dialog.open();
      }, 1000);
    }
  },
  template: `
    <div>
      <k-button icon="open" @click="open">Open Dialog</k-button>

      <k-dialog
        ref="dialog"
        text="This is a dialog that demonstrates the preloading effect for async stuff"
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
      />
    </div>
  `,
});

export const errorNotification = () => ({
  extends: regular(),
  methods: {
    onSubmit() {
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
      />
    </div>
  `,
});

export const successNotification = () => ({
  extends: regular(),
  methods: {
    onSubmit() {
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
        @cancel="onCancel"
        @close="onClose"
        @open="onOpen"
        @submit="onSubmit"
      />
    </div>
  `,
});
