import TelField from "./TelField.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Tel Field",
  component: TelField
};

export const regular = () => ({
  data() {
    return {
      value: ""
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-tel-field
        v-model="value"
        label="Phone"
        class="mb-8"
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
      <k-tel-field
        v-model="value"
        label="Phone"
        class="mb-8"
        placeholder="+49 1234 5678"
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
      <k-tel-field
        v-model="value"
        :autofocus="true"
        label="Phone"
        class="mb-8"
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
      <k-tel-field
        v-model="value"
        :disabled="true"
        label="Phone"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const beforeAndAfter = () => ({
  ...regular(),
  template: `
    <div>
      <k-tel-field
        v-model="value"
        label="Phone"
        before="+49"
        after="-0"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
