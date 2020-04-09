import DateTimeInput from "./DateTimeInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / DateTime Input",
  component: DateTimeInput
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
      <k-datetime-input
        v-model="value"
        @input="input"
      />
    </k-docs-input>
  `,
});


