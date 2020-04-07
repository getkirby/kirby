import ToggleInput from "./ToggleInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Toggle Input",
  component: ToggleInput
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
      <k-toggle-input
        v-model="value"
        text="This is a toggle"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const toggleText = () => ({
  ...regular(),
  template: `
    <div>
      <k-toggle-input
        v-model="value"
        :text="['No', 'Yes']"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const autofocus   = () => ({
  ...regular(),
  template: `
    <div>
      <k-toggle-input
        v-model="value"
        :autofocus="true"
        text="This is a toggle"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const disabled = () => ({
  template: `
    <k-toggle-input
      v-model="value"
      :disabled="true"
      text="This is a toggle"
    />
  `
});

export const noText = () => ({
  ...regular(),
  template: `
    <k-toggle-input v-model="value" />
  `
});
