import RadioInput from "./RadioInput.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Form / Input / Radio Input",
  component: RadioInput,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: "",
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
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-radio-input
        v-model="value"
        :options="options"
        class="mb-6"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});


export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-radio-input
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-6"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-radio-input
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-6"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const columns = () => ({
  ...regular(),
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-radio-input
        v-model="value"
        :columns="3"
        :options="options"
        class="mb-6"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

