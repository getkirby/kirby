import SelectField from "./SelectField.vue";
import Padding from "../storybook/Padding.js";
import Options from "../storybook/Options.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Select Field",
  component: SelectField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: 2,
    };
  },
  computed: {
    options() {
      return Options(10);
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-select-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Select"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const placeholder = () => ({
  extends: regular(),
  template: `
    <div>
      <k-select-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Select"
        placeholder="Please select something …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const autofocus = () => ({
  extends: regular(),
  template: `
    <div>
      <k-select-field
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-8"
        label="Select"
        placeholder="Please select something …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const groups = () => ({
  extends: regular(),
  data() {
    return {
      value: "b",
    };
  },
  computed: {
    options() {
      return [
        {
          group: "Letters",
          options: [
            { value: "a", text: "A" },
            { value: "b", text: "B" },
            { value: "c", text: "C" }
          ]
        },
        {
          group: "Numbers",
          options: [
            { value: "1", text: "1" },
            { value: "2", text: "2" },
            { value: "3", text: "3" }
          ]
        }
      ];
    }
  },
  template: `
    <div>
      <k-select-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Select"
        placeholder="Please select something …"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
