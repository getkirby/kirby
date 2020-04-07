import MultiselectInput from "./MultiselectInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Multiselect Input",
  component: MultiselectInput
};

export const regular = () => ({
  data() {
    return {
      value: [],
    };
  },
  computed: {
    options() {
      return [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ];
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-multiselect-input
        v-model="value"
        :options="options"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});


