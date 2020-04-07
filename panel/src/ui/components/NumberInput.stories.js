import NumberInput from "./NumberInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Number Input",
  component: NumberInput
};

export const regular = () => ({
  data() {
    return {
      value: null
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-number-input
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
      <k-number-input
        v-model="value"
        placeholder="Enter a number …"
        @input="input"
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
      <k-number-input
        v-model="value"
        :autofocus="true"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const minMax = () => ({
  ...regular(),
  template: `
    <div>
      <k-number-input
        v-model="value"
        :min="5"
        :max="10"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const step = () => ({
  ...regular(),
  template: `
    <div>
      <k-number-input
        v-model="value"
        :step="0.1"
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
      <k-number-input
        v-model="value"
        :disabled="true"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

