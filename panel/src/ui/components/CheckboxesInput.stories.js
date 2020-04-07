import CheckboxesInput from "./CheckboxesInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Checkboxes Input",
  component: CheckboxesInput
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
      <k-checkboxes-input
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


export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-checkboxes-input
        v-model="value"
        :autofocus="true"
        :options="options"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-checkboxes-input
        v-model="value"
        :disabled="true"
        :options="options"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const columns = () => ({
  ...regular(),
  computed: {
    columns() {
      return 3;
    },
    options: regular().computed.options
  },
  template: `
    <div>
      <k-checkboxes-input
        v-model="value"
        :columns="columns"
        :options="options"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

