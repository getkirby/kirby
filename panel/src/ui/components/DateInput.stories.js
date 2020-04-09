import DateInput from "./DateInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Date Input",
  component: DateInput
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
    <k-docs-input :value="value">
      <k-date-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </k-docs-input>
  `,
});


