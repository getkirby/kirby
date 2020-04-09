import FormDialog from "./FormDialog.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Dialog / Form Dialog",
  component: FormDialog
};

export const regular = () => ({
  methods: {
    cancel: action("cancel"),
    close: action("close"),
    input: action("input"),
    open: action("open"),
    submit: action("submit")
  },
  data() {
    return {
      values: {}
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        email: {
          label: "Email",
          type: "email",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "text"
        }
      };
    }
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-form-dialog
        ref="dialog"
        :fields="fields"
        :value="values"
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
  ...regular(),
  data() {
    return {
      values: {
        name: "Peter Jackson",
        email: "peter@wetaworkshop.com",
        text: "Oh lord"
      }
    };
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
      <k-form-dialog
        ref="dialog"
        :fields="fields"
        :value="values"
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

