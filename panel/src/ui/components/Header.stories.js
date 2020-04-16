import Header from "./Header.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";
import { withKnobs, text } from '@storybook/addon-knobs';

export default {
  title: "Layout / Header",
  decorators: [withKnobs, Padding],
  component: Header,
};

export const simple = () => ({
  template: `
    <k-header>{{ text }}</k-header>
  `,
  props: {
    text: {
      default: text('text', 'Headline')
    },
  }
});

export const editable = () => ({
  template: `
    <k-header :editable="true" @edit="edit">{{ text }}</k-header>
  `,
  props: {
    text: {
      default: text('text', 'Headline')
    },
  },
  methods: {
    edit: action('edit')
  }
});

export const withOptions = () => ({
  template: `
    <k-header>
      {{ text }}

      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>
    </k-header>
  `,
  props: {
    text: {
      default: text('text', 'Headline')
    },
  }
});

export const bellsAndWhistles = () => ({
  template: `
    <k-header>
      {{ text }}

      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>

      <k-prev-next slot="right" />
    </k-header>
  `,
  props: {
    text: {
      default: text('text', 'Headline')
    },
  }
});

export const withTabs = () => ({
  props: {
    text: {
      default: text("text", "Headline")
    }
  },
  computed: {
    tabs() {
      return [
        { name: "content", label: "Content", icon: "page" },
        { name: "seo", label: "SEO", icon: "search" }
      ]
    }
  },
  template: `
    <k-header :tabs="tabs" :tab="tabs[0]">
      {{ text }}

      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>

      <k-prev-next slot="right" />
    </k-header>
  `,
});
