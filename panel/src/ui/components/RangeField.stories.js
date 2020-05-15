import RangeField from "./RangeField.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Range Field",
  component: RangeField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: 0,
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-range-field
        v-model="value"
        class="mb-8"
        label="Range"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const step = () => ({
  ...regular(),
  template: `
    <div>
      <k-range-field
        v-model="value"
        :step="10"
        class="mb-8"
        label="Range"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const minMax = () => ({
  ...regular(),
  data() {
    return {
      value: 25
    };
  },
  template: `
    <div>
      <k-range-field
        v-model="value"
        :min="10"
        :max="50"
        class="mb-8"
        label="Range"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const tooltip = () => ({
  ...regular(),
  template: `
    <div>
      <k-range-field
        v-model="value"
        :tooltip="{ before: '€', after: ' / per month' }"
        class="mb-8"
        label="Range"
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
      <k-range-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Range"
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
      <k-range-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Range"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
