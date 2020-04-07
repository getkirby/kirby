import TimeInput from "./DateInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Time Input",
  component: TimeInput
};

export const regular = () => ({
  data() {
    return {
      value: "",
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-time-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const amPm = () => ({
  ...regular(),
  template: `
    <div>
      <k-time-input
        v-model="value"
        :notation="12"
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
      <k-time-input
        v-model="value"
        :step="1"
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
      <k-time-input
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
      <k-time-input
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
