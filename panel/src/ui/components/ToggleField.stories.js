import ToggleField from "./ToggleField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Toggle Field",
  component: ToggleField,
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
      <k-toggle-field
        v-model="value"
        class="mb-8"
        label="Toggle"
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
      <k-toggle-field
        v-model="value"
        class="mb-8"
        label="Toggle"
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
      <k-toggle-field
        v-model="value"
        class="mb-8"
        label="Toggle"
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
      <k-toggle-field
        v-model="value"
        class="mb-8"
        label="Toggle"
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
      <k-toggle-field
        v-model="value"
        class="mb-8"
        label="Toggle"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
