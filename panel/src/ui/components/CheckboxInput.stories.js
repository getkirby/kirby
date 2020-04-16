import CheckboxInput from "./CheckboxInput.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Checkbox Input",
  component: CheckboxInput,
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      value: false
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-checkbox-input
        v-model="value"
        label="This is a checkbox"
        class="mb-6 block"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-checkbox-input
        v-model="value"
        :autofocus="true"
        label="This is a checkbox"
        class="mb-6 block"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-checkbox-input
        v-model="value"
        :disabled="true"
        label="This is a checkbox"
        class="mb-6 block"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

