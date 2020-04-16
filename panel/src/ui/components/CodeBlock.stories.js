import CodeBlock from "./CodeBlock.vue";
import Padding from "../storybook/Padding.js";
import { withKnobs, text } from '@storybook/addon-knobs';

export default {
  title: "Typography / Code Block",
  decorators: [withKnobs, Padding],
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

