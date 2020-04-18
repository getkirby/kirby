import Dropdown from "./Dropdown.vue";
import DropdownContent from "./DropdownContent.vue";
import DropdownItem from "./DropdownItem.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";
import { withKnobs } from '@storybook/addon-knobs';

export default {
  title: "UI | Dropdown / Base Dropdown",
  decorators: [withKnobs, Padding],
  component: Dropdown,
  subcomponents: {
    DropdownContent,
    DropdownItem
  }
};

export const darkTheme = () => ({
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

export const lightTheme = () => ({
  ...darkTheme(),
  template: `
    <k-dropdown>
      <k-button icon="angle-down" @click="$refs.dropdown.toggle()">Open dropdown</k-button>
      <k-dropdown-content
        ref="dropdown"
        theme="light"
        @open="open"
        @close="close"
      >
        <k-dropdown-item icon="edit" @click="edit">Edit</k-dropdown-item>
        <k-dropdown-item icon="trash" @click="remove">Remove</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
  `
});

export const divider = () => ({
  ...darkTheme(),
  template: `
    <k-dropdown>
      <k-button icon="angle-down" @click="$refs.dropdown.toggle()">Open dropdown</k-button>
      <k-dropdown-content
        ref="dropdown"
        @open="open"
        @close="close"
      >
        <k-dropdown-item icon="edit" @click="edit">Edit</k-dropdown-item>
        <k-dropdown-item icon="copy" @click="edit">Duplicate</k-dropdown-item>
        <hr>
        <k-dropdown-item icon="trash" @click="remove">Remove</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
  `
});

