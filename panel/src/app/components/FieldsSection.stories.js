import FieldsSection from "./FieldsSection.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "App | Blueprints / Fields Section",
  component: FieldsSection,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      values: {}
    };
  },
  computed: {
    disabled() {
      return false;
    },
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
      <k-fields-section
        :disabled="disabled"
        :fields="fields"
        :values="values"
        class="mb-10"
      />

      <k-headline class="mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

export const disabled = () => ({
  extends: regular(),
  computed: {
    disabled() {
      return true;
    }
  }
});
