import PasswordInput from "./PasswordInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Password Input",
  component: PasswordInput
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
      <k-password-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-password-input
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
      <k-password-input
        v-model="value"
        :disabled="true"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});
