import FormDrawer from "./FormDrawer.vue";
import Padding from "../storybook/Padding.js";
import {
  regular as regularFormDialog,
  prefilled as prefilledFormDialog
} from "./FormDialog.stories.js";

export default {
  title: "UI | Dialog / Form Drawer",
  component: FormDrawer,
  decorators: [Padding]
};

export const regular = () => ({
  extends: regularFormDialog(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Drawer</k-button>
      <k-form-drawer
        ref="dialog"
        :fields="fields"
        :value="values"
        title="Edit contact"
        @cancel="cancel"
        @close="close"
        @input="input"
        @open="open"
        @submit="submit"
      />

      <k-headline class="mt-8 mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

export const prefilled = () => ({
  extends: prefilledFormDialog(),
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Drawer</k-button>
      <k-form-drawer
        ref="dialog"
        :fields="fields"
        :value="values"
        title="Edit contact"
        @cancel="cancel"
        @close="close"
        @input="input"
        @open="open"
        @submit="submit"
      />

      <k-headline class="mt-8 mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

