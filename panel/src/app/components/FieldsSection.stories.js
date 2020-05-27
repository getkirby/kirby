import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "App | Blueprints / Fields Section",
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: {}
    };
  },
  computed: {
    disabled() {
      return false;
    },
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
          type: "textarea"
        }
      };
    }
  },
  methods: {
    onFocus: action("focus"),
    onInput: action("input"),
    onSubmit: action("submit")
  },
  template: `
    <div>
      <k-fields-section
        :disabled="disabled"
        :fields="fields"
        :value="value"
        class="mb-10"
        @focus="onFocus"
        @input="onInput"
        @submit="onSubmit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  extends: regular(),
  computed: {
    disabled() {
      return true;
    }
  }
});
