import Headline from "./Headline.vue";
import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Typography / Headline",
  decorators: [withKnobs],
  component: Headline
};

export const configurator = () => ({
  template: '<k-headline v-bind="$props">{{ text }}</k-headline>',
  props: {
    size: {
      default: select('size', ['small', 'regular', 'large', 'huge'], 'regular')
    },
    tag: {
      default: text('tag', 'h2')
    },
    text: {
      default: text('text', 'Headline')
    },
    theme: {
      default: select('theme', ['none', 'positive', 'negative'], 'none')
    },
  }
});

