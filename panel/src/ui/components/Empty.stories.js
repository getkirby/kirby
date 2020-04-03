import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "Empty",
  decorators: [withKnobs]
};

export const configurator = () => ({
  props: {
    icon: {
      default: text("icon", "page")
    },
    layout: {
      default: select("layout", ["list", "cards"])
    },
    text: {
      default: text("text", "This is an empty state")
    }
  },
  template: `
    <k-empty :icon="icon" :layout="layout">{{ text }}</k-empty>
  `,
});

