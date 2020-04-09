import UrlField from "./UrlField.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / URL Field",
  component: UrlField
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
      <k-url-field
        v-model="value"
        class="mb-8"
        label="URL"
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
      <k-url-field
        v-model="value"
        class="mb-8"
        label="URL"
        placeholder="https://getkirby.com"
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
      <k-url-field
        v-model="value"
        class="mb-8"
        label="URL"
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
      <k-url-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="URL"
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
      <k-url-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="URL"
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
      <k-url-field
        v-model="value"
        before="http://"
        after=".com"
        class="mb-8"
        label="URL"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
