import Button from "./Button.vue";
import { action } from "@storybook/addon-actions";
import Padding from "../storybook/Padding.js";

export default {
  title: "Navigation / Button",
  component: Button,
  decorators: [Padding]
};

export const textOnly = () => ({
  methods: {
    action: action('clicked')
  },
  template: '<k-button @click="action">Text Button</k-button>',
});

export const textAndIcon = () => ({
  ...textOnly(),
  template: '<k-button icon="edit" @click="action">Icon & Text</k-button>',
});

export const iconOnly = () => ({
  ...textOnly(),
  template: '<k-button icon="edit" />',
});

export const link = () => ({
  ...textOnly(),
  template: '<k-button icon="url" @click="action" link="https://getkirby.com">Link</k-button>'
});

export const positive = () => ({
  ...textOnly(),
  template:
    '<k-button icon="check" theme="positive" @click="action">Nice one!</k-button>'
});

export const negative = () => ({
  ...textOnly(),
  template:
    '<k-button icon="trash" theme="negative" @click="action">Uh oh!</k-button>'
});

export const disabled = () => ({
  ...textOnly(),
  template: '<k-button :disabled="true" icon="trash" @click="action">Disabled button</k-button>',
});

export const group = () => ({
  methods: {
    edit: action('edit'),
    remove: action('remove')
  },
  template: `
    <k-button-group>
      <k-button icon="edit" @click="edit">Edit</k-button>
      <k-button icon="trash" @click="remove">Remove</k-button>
    </k-button-group>
  `,
});
