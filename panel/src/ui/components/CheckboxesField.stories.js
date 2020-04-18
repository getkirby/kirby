import CheckboxesField from "./CheckboxesField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Checkboxes Field",
  component: CheckboxesField,
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
      <k-checkboxes-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Checkboxes"
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
    <k-checkboxes-field
      v-model="value"
      :disabled="true"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <k-checkboxes-field
      v-model="value"
      :autofocus="true"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});

export const columns = () => ({
  ...regular(),
  template: `
    <k-checkboxes-field
      v-model="value"
      :columns="3"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});


