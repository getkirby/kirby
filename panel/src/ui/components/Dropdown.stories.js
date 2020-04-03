import { action } from "@storybook/addon-actions";
import { withKnobs, text } from '@storybook/addon-knobs';

export default {
  title: "Dropdown",
  decorators: [withKnobs]
};

export const simple = () => ({
  template: `
    <k-dropdown>
      <k-button icon="angle-down" @click="$refs.dropdown.toggle()">Open dropdown</k-button>
      <k-dropdown-content ref="dropdown" @open="open" @close="close">
        <k-dropdown-item icon="edit" @click="edit">Edit</k-dropdown-item>
        <k-dropdown-item icon="trash" @click="remove">Remove</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
  `,
  methods: {
    close: action('Dropdown closed'),
    edit: action('Edit button clicked'),
    open: action('Dropdown opened'),
    remove: action('Remove button clicked')
  }
});

