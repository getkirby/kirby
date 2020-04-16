import DateField from "./DateField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Date Field",
  component: DateField,
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
      <k-date-field
        v-model="value"
        class="mb-8"
        label="Date"
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
      <k-date-field
        v-model="value"
        :time="{ notation: 12 }"
        class="mb-8"
        label="Date"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const noTime = () => ({
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
      <k-date-field
        v-model="value"
        :time="false"
        class="mb-8"
        label="Date"
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
      <k-date-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Date"
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
      <k-date-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Date"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});






