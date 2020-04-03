import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Text",
  decorators: [withKnobs]
};

export const configurator = () => ({
  template: '<k-text v-bind="$props">{{ text }}</k-text>',
  props: {
    align: {
      default: select('align', ['left', 'center', 'right'], 'left')
    },
    size: {
      default: select('size', ['tiny', 'small', 'regular', 'large'], 'regular')
    },
    text: {
      default: text('text', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.')
    },
    theme: {
      default: select('theme', ['none', 'help'], 'none')
    },
  }
});

