import UrlInput from "./UrlInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / URL Input",
  component: UrlInput
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
      <k-url-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const placeholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-url-input
        v-model="value"
        placeholder="https://example.com"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const noPlaceholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-url-input
        v-model="value"
        placeholder=""
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-url-input
        v-model="value"
        :autofocus="true"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-url-input
        v-model="value"
        :disabled="true"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});
