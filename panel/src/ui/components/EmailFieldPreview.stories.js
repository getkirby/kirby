import EmailFieldPreview from "./EmailFieldPreview.vue";
import { withKnobs, text, object } from '@storybook/addon-knobs';

export default {
  title: "Form / Field / Preview / Email Field Preview",
  decorators: [withKnobs],
  component: EmailFieldPreview
};

export const regular = () => ({
  props: {
    value: {
      default: text('value', 'homer@simpson.org')
    }
  },
  template: `
    <div>
      <k-email-field-preview
        :value="value"
        class="mb-8"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const withColumn = () => ({
  props: {
    value: {
      default: text('value', 'homer@simpson.org')
    },
    column: {
      default: object('column', { before: "Write to", after: "now" })
    }
  },
  template: `
    <div>
      <k-email-field-preview
        :value="value"
        :column="column"
        class="mb-8"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" class="mb-3" />

      <k-headline class="mb-3">Column</k-headline>
      <k-code-block :code="column" />
    </div>
  `,
});
