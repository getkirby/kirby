import MultiselectField from "./MultiselectField.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Multiselect Field",
  component: MultiselectField,
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
      <k-multiselect-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Multiselect"
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
      <k-multiselect-field
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-8"
        label="Multiselect"
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
      <k-multiselect-field
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-8"
        label="Multiselect"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});
