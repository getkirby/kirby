import TextInput from "./TextInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Text Input",
  component: TextInput
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
      <k-text-input
        v-model="value"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const withPlaceholder = () => ({
  ...regular(),
  template: `
    <div>
      <k-text-input
        v-model="value"
        placeholder="Type something …"
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
      <k-text-input
        v-model="value"
        :autofocus="true"
        placeholder="Type something …"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});
