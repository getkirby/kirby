import StructureField from "./StructureField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Structure Field",
  component: StructureField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: [
        { platform: "Twitter", url: "https://twitter.com/getkirby" },
        { platform: "Instagram", url: "https://instagram.com/getkirby" },
        { platform: "Github", url: "https://github.com/getkirby" }
      ]
    };
  },
  computed: {
    columns() {
      return {
        platform: {
          label: "Platform"
        },
        url: {
          label: "URL"
        }
      };
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        label="Social"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

