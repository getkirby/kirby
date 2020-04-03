import { action } from "@storybook/addon-actions";
import { withKnobs, text, boolean, select } from '@storybook/addon-knobs';

export default {
  title: "Button",
  decorators: [withKnobs]
};


export const configurator = () => ({
  template: '<k-button v-bind="$props">{{ text }}</k-button>',
  props: {
    disabled: {
      default: boolean('disabled', false)
    },
    icon: {
      default: text('icon', 'edit')
    },
    link: {
      default: text('link', null)
    },
    target: {
      default: text('target', null)
    },
    theme: {
      default: select('theme', ["none", "positive", "negative"])
    },
    text: {
      default: text('text', 'Edit')
    }
  },
});

export const textOnly = () => ({
  template: '<k-button @click="action">Text Button</k-button>',
  methods: {
    action: action('clicked')
  }
});

export const textAndIcon = () => ({
  template: '<k-button icon="edit" @click="action">Icon & Text</k-button>',
  methods: {
    action: action('clicked')
  }
});

export const iconOnly = () => ({
  template: '<k-button icon="edit" />',
  methods: {
    action: action('clicked')
  }
});

export const disabled = () => ({
  template: '<k-button :disabled="true" icon="trash" @click="action">Disabled button</k-button>',
  methods: {
    action: action('clicked')
  }
});

export const group = () => ({
  template: `
    <k-button-group>
      <k-button icon="edit" @click="edit">Edit</k-button>
      <k-button icon="trash" @click="remove">Remove</k-button>
    </k-button-group>
  `,
  methods: {
    edit: action('edit'),
    remove: action('remove')
  }
});
