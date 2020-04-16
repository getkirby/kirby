import TimeField from "./TimeField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Time Field",
  component: TimeField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: "",
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-time-field
        v-model="value"
        class="mb-8"
        label="Time"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const amPm = () => ({
  ...regular(),
  template: `
    <div>
      <k-time-field
        v-model="value"
        :notation="12"
        class="mb-8"
        label="Time"
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
      <k-time-field
        v-model="value"
        :step="1"
        class="mb-8"
        label="Time"
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
      <k-time-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Time"
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
      <k-time-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Time"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

