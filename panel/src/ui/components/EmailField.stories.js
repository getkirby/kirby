import EmailField from "./EmailField.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Email Field",
  component: EmailField
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
      <k-email-field
        v-model="value"
        label="Email"
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
      <k-email-field
        v-model="value"
        label="Email"
        class="mb-8"
        placeholder="Your email …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const noPlaceholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-email-field
        v-model="value"
        label="Email"
        class="mb-8"
        placeholder=""
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
      <k-email-field
        v-model="value"
        label="Email"
        :autofocus="true"
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
      <k-email-field
        v-model="value"
        label="Email"
        :disabled="true"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
