import Vue from "vue";
import Toolbar from "./Toolbar.vue";
import Padding from "../../../storybook/theme/Padding.js";
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
      <k-toolbar
        :layout="[
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
  computed: {
    buttons() {
      return {
        hr: () => {
          return {
            label: "Horizontal line",
            icon: "dots",
            command: "insert",
            args: "****",
          };
        }
      };
    }
  },
  methods: {
    command: action("command")
  },
  template: `
    <div style="position: relative">
      <k-toolbar
        :buttons="buttons"
        :layout="[
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


export const shortcut = () => ({
  methods: {
    command: action("command")
  },
  template: `
    <div class="relative">
      <k-toolbar
        ref="toolbar"
        class="mb-6"
        @command="command"
      />

      <k-headline class="mb-3">Shortcuts</k-headline>
      <ul>
        <li><k-button @click="$refs.toolbar.shortcut('b')">$refs.toolbar.shortcut("b")</k-button></li>
        <li><k-button @click="$refs.toolbar.shortcut('i')">$refs.toolbar.shortcut("i")</k-button></li>
        <li><k-button @click="$refs.toolbar.shortcut('k')">$refs.toolbar.shortcut("k")</k-button></li>
        <li><k-button @click="$refs.toolbar.shortcut('e')">$refs.toolbar.shortcut("e")</k-button></li>
      </ul>
    </div>
  `
});

export const options = () => ({
  methods: {
    command: action("command")
  },
  template: `
    <div style="position: relative">
      <k-toolbar
        :options="{
          headings: {
            levels: 6
          },
          file: {
            select: false
          }
        }"
        @command="command"
      />
    </div>
  `
});
