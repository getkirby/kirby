import NumberField from "./NumberField.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Number Field",
  component: NumberField
};

export const regular = () => ({
  data() {
    return {
      value: null
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-number-field
        v-model="value"
        class="mb-8"
        label="Number"
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
      <k-number-field
        v-model="value"
        class="mb-8"
        label="Number"
        placeholder="Enter a number …"
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
      <k-number-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Number"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const minMax = () => ({
  ...regular(),
  template: `
    <div>
      <k-number-field
        v-model="value"
        :min="5"
        :max="10"
        class="mb-8"
        label="Number"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const step = () => ({
  ...regular(),
  template: `
    <div>
      <k-number-field
        v-model="value"
        :step="0.1"
        class="mb-8"
        label="Number"
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
      <k-number-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Number"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

