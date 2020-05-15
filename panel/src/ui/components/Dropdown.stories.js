import Dropdown from "./Dropdown.vue";
import DropdownContent from "./DropdownContent.vue";
import DropdownItem from "./DropdownItem.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dropdown / Base Dropdown",
  decorators: [Padding],
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

export const buttonColors = () => ({
  ...darkTheme(),
  template: `
    <k-button-group>
      <k-dropdown>
        <k-button icon="angle-down" @click="$refs.dark.toggle()">Dark dropdown</k-button>
        <k-dropdown-content
          ref="dark"
          @open="open"
          @close="close"
        >
          <k-dropdown-item color="green-light" icon="edit" @click="edit">Edit</k-dropdown-item>
          <k-dropdown-item color="purple-light" icon="copy" @click="edit">Duplicate</k-dropdown-item>
          <hr>
          <k-dropdown-item color="red-light" icon="trash" @click="remove">Remove</k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
      <k-dropdown>
        <k-button icon="angle-down" @click="$refs.light.toggle()">Light dropdown</k-button>
        <k-dropdown-content
          ref="light"
          theme="light"
          @open="open"
          @close="close"
        >
          <k-dropdown-item color="green" icon="edit" @click="edit">Edit</k-dropdown-item>
          <k-dropdown-item color="purple" icon="copy" @click="edit">Duplicate</k-dropdown-item>
          <hr>
          <k-dropdown-item color="red" icon="trash" @click="remove">Remove</k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
    </k-button-group>
  `
});

export const iconColors = () => ({
  ...darkTheme(),
  template: `
    <k-button-group>
      <k-dropdown>
        <k-button icon="angle-down" @click="$refs.dark.toggle()">Dark dropdown</k-button>
        <k-dropdown-content
          ref="dark"
          @open="open"
          @close="close"
        >
          <k-dropdown-item :icon="{type: 'edit', color: 'green-light'}" @click="edit">Edit</k-dropdown-item>
          <k-dropdown-item :icon="{type: 'copy', color: 'purple-light'}" @click="edit">Duplicate</k-dropdown-item>
          <hr>
          <k-dropdown-item :icon="{type: 'trash', color: 'red-light'}" @click="remove">Remove</k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
      <k-dropdown>
        <k-button icon="angle-down" @click="$refs.light.toggle()">Light dropdown</k-button>
        <k-dropdown-content
          ref="light"
          theme="light"
          @open="open"
          @close="close"
        >
          <k-dropdown-item :icon="{type: 'edit', color: 'green-light'}" @click="edit">Edit</k-dropdown-item>
          <k-dropdown-item :icon="{type: 'copy', color: 'purple-light'}" @click="edit">Duplicate</k-dropdown-item>
          <hr>
          <k-dropdown-item :icon="{type: 'trash', color: 'red-light'}" @click="remove">Remove</k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
    </k-button-group>
  `
});
