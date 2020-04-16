import MultiselectInput from "./MultiselectInput.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Multiselect Input",
  component: MultiselectInput,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: [],
    };
  },
  computed: {
    options() {
      return [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ];
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-multiselect-input
        v-model="value"
        :options="options"
        class="mb-6"
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
      <k-multiselect-input
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-multiselect-input
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});


