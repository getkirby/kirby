import SelectInput from "./SelectInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Select Input",
  component: SelectInput
};

export const regular = () => ({
  data() {
    return {
      value: "b",
      options: [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ]
    };
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-select-input
        v-model="value"
        :options="options"
        @input="input"
      />

      <br>

      Value: {{ value }}
    </div>
  `,
});

export const placeholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-select-input
        v-model="value"
        :options="options"
        placeholder="Please select something …"
        @input="input"
      />

      <br>

      Value: {{ value }}
    </div>
  `
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-select-input
        v-model="value"
        :autofocus="true"
        :options="options"
        placeholder="Please select something …"
        @input="input"
      />

      <br>

      Value: {{ value }}
    </div>
  `
});

