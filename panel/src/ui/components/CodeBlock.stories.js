import CodeBlock from "./CodeBlock.vue";
import { withKnobs, text } from '@storybook/addon-knobs';

export default {
  title: "Typography / Code Block",
  decorators: [withKnobs],
  component: CodeBlock
};

export const regular = () => ({
  props: {
    code: {
      default: text('code', '// this is some nice code')
    }
  },
  template: '<k-code-block :code="code" />',
});

