import Vue from "vue";
import Toolbar from "./Toolbar.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Foundation / Toolbar",
  component: Toolbar,
  decorators: [Padding]
};

export const regular = () => ({
  methods: {
    command: action("command")
  },
  template: `
    <div style="position: relative">
      <k-toolbar @command="command" />
    </div>
  `,
});

export const customLayout = () => ({
  methods: {
    command: action("command")
  },
  template: `
    <div style="position: relative">
      <k-toolbar :buttons="[
          'bold',
          'italic',
          '|',
          'link',
          'email',
          '|',
          'ul',
          'ol'
        ]"
      @command="command"
    />
    </div>
  `,
});

Vue.component("k-toolbar-hr-button", {
  template: `
    <k-button
      tooltip="Horizontal line"
      icon="dots"
      class="k-toolbar-button"
      @click="$emit('command', 'insert', '****')"
    />
  `
});

export const customButton = () => ({
  methods: {
    command: action("command")
  },
  template: `
    <div style="position: relative">
      <k-toolbar :buttons="[
          'bold',
          'italic',
          '|',
          'link',
          'email',
          '|',
          'ul',
          'ol',
          '|',
          'hr'
        ]"
      @command="command"
    />
    </div>
  `,
});

