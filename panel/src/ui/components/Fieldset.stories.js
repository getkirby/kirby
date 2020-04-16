import Fieldset from "./Fieldset.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Foundation / Fieldset",
  component: Fieldset,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      values: {}
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        email: {
          label: "Email",
          type: "email",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "textarea"
        }
      };
    }
  },
  methods: {
    input: action("input"),
    focus: action("focus"),
    submit: action("submit")
  },
  template: `
    <div>
      <k-fieldset
        :fields="fields"
        v-model="values"
        class="mb-8"
        @focus="focus"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

