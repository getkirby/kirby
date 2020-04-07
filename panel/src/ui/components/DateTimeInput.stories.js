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
    <div>
      <k-datetime-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});


