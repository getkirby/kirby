import Box from "./Box.vue";
import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Layout / Box",
  decorators: [withKnobs],
  component: Box
};

export const configurator = () => ({
  template: '<k-box v-bind="$props">{{ text }}</k-box>',
  props: {
    text: {
      default: text('text', 'Box text')
    },
    theme: {
      default: select('theme', ['default', 'none', 'info', 'positive', 'negative'], 'default')
    },
  }
});

export const positive = () => ({
  template: '<k-box theme="positive">A nice box</k-box>',
});

export const negative = () => ({
  template: '<k-box theme="negative">A warning box</k-box>',
});

export const info = () => ({
  template: '<k-box theme="info">A neutral info box</k-box>',
});
