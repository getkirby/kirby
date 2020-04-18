import SelectField from "./SelectField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Select Field",
  component: SelectField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: "b",
      options: [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ]
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-select-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Select"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const placeholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-select-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Select"
        placeholder="Please select something …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-select-field
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-8"
        label="Select"
        placeholder="Please select something …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

