import CheckboxInput from "./CheckboxInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Checkbox Input",
  component: CheckboxInput
};

export const regular = () => ({
  data() {
    return {
      value: false
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-checkbox-input
        v-model="value"
        label="This is a checkbox"
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
      <k-checkbox-input
        v-model="value"
        :autofocus="true"
        label="This is a checkbox"
        @input="input"
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
      <k-checkbox-input
        v-model="value"
        :disabled="true"
        label="This is a checkbox"
        @input="input"
      />
    </div>
  `
       });

