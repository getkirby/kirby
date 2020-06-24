import Link from "./Link.vue";
import { withKnobs, text, boolean } from '@storybook/addon-knobs';

export default {
  title: "Navigation / Link",
  decorators: [withKnobs],
  component: Link
};

export const configurator = () => ({
  template: '<k-link v-bind="$props">{{ text }}</k-link>',
  props: {
    disabled: {
      default: boolean('disabled', false)
    },
    rel: {
      default: text('rel', 'me')
    },
    text: {
      default: text('text', 'Link text')
    },
    title: {
      default: text('title', 'Appears when you hover over the link')
    },
    to: {
      default: text('to', 'https://getkirby.com')
    }
  }
});

export const disabled = () => ({
  template: '<k-link :disabled="true" to="https://getkirby.com">Disabled link</k-link>',
});

export const withoutURL = () => ({
  template: '<k-link>No href attribute</k-link>',
});

export const targetBlank = () => ({
  template: '<k-link to="https://getkirby.com" target="_blank">Opens in a new tab</k-link>',
});


