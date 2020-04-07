import RangeInput from "./RangeInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Range Input",
  component: RangeInput
};

export const regular = () => ({
  data() {
    return {
      value: 0,
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-range-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const step = () => ({
  ...regular(),
  template: `
    <div>
      <k-range-input
        v-model="value"
        :step="10"
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
  data() {
    return {
      value: 25
    };
  },
  template: `
    <div>
      <k-range-input
        v-model="value"
        :min="10"
        :max="50"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const tooltip = () => ({
  ...regular(),
  template: `
    <div>
      <k-range-input
        v-model="value"
        :tooltip="{ before: '€', after: ' / per month' }"
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
      <k-range-input
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

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-range-input
        v-model="value"
        :disabled="true"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});
