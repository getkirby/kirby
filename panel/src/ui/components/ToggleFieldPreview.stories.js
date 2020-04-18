import ToggleFieldPreview from "./ToggleFieldPreview.vue";
import Padding from "../storybook/Padding.js";
import { withKnobs, object, boolean } from '@storybook/addon-knobs';
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field Preview / Toggle Field Preview",
  decorators: [withKnobs, Padding],
  component: ToggleFieldPreview
};

export const regular = () => ({
  props: {
    value: {
      default: boolean('value', true)
    }
  },
  data() {
    return {
      column: {
        text: false
      }
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-toggle-field-preview
        :value="value"
        :column="column"
        @input="input"
        class="mb-8"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const withText = () => ({
  props: {
    value: {
      default: boolean('value', true)
    },
    field: {
      default: object('field', { text: ["This is off", "This is on"] })
    },
    column: {
      default: object('column', { text: true })
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-toggle-field-preview
        :value="value"
        :column="column"
        :field="field"
        @input="input"
        class="mb-8"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block class="mb-6" :code="value" />

      <k-headline class="mb-3">Field</k-headline>
      <k-code-block class="mb-6" :code="field" />

      <k-headline class="mb-3">Column</k-headline>
      <k-code-block :code="column" />
    </div>
  `,
});
