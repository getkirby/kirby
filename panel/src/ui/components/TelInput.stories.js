import TelInput from "./TelInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Tel Input",
  component: TelInput
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
      <k-tel-input
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
      <k-tel-input
        v-model="value"
        placeholder="+49 1234 5678"
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
      <k-tel-input
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
      <k-tel-input
        v-model="value"
        :disabled="true"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});
