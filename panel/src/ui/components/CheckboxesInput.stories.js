import CheckboxesInput from "./CheckboxesInput.vue";
import Padding from "../storybook/Padding.js";
import Options from "../storybook/Options.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Input / Checkboxes Input",
  component: CheckboxesInput,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: [],
    };
  },
  computed: {
    options() {
      return Options(3);
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-checkboxes-input
        v-model="value"
        :options="options"
        class="mb-6"
        @input="input"
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
      <k-checkboxes-input
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-6"
        @input="input"
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
      <k-checkboxes-input
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const columns = () => ({
  extends: regular(),
  computed: {
    columns() {
      return 3;
    }
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-checkboxes-input
        v-model="value"
        :columns="3"
        :options="options"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

