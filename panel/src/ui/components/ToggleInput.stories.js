import ToggleInput from "./ToggleInput.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Input / Toggle Input",
  component: ToggleInput,
  decorators: [Padding]
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
      <k-toggle-input
        v-model="value"
        class="mb-6"
        text="This is a toggle"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const toggleText = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-toggle-input
        v-model="value"
        class="mb-6"
        :text="['No', 'Yes']"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const autofocus   = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-toggle-input
        v-model="value"
        class="mb-6"
        :autofocus="true"
        :text="['No', 'Yes']"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const disabled = () => ({
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-toggle-input
        v-model="value"
        class="mb-6"
        :disabled="true"
        :text="['No', 'Yes']"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const noText = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-toggle-input
        v-model="value"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
