import UrlFieldPreview from "./UrlFieldPreview.vue";
import { withKnobs, text, object } from '@storybook/addon-knobs';

export default {
  title: "Form / Field / Preview / URL Field Preview",
  decorators: [withKnobs],
  component: UrlFieldPreview
};

export const regular = () => ({
  props: {
    value: {
      default: text('value', 'https://getkirby.com')
    }
  },
  template: `
    <div>
      <k-url-field-preview
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
      default: text('value', 'https://getkirby.com')
    },
    column: {
      default: object('column', { before: "Go to", after: "now" })
    }
  },
  template: `
    <div>
      <k-url-field-preview
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
