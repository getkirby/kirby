import PasswordField from "./PasswordField.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Password Field",
  component: PasswordField
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
      <k-password-field
        v-model="value"
        class="mb-8"
        label="Password"
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
      <k-password-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Password"
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
      <k-password-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Password"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
