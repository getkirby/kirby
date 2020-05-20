import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Autocomplete",
  decorators: [Padding]
};

export const simple = () => ({
  template: `<k-header>Headline</k-header>`
});

export const editable = () => ({
  methods: {
    edit: action('edit')
  },
  template: `
    <k-header :editable="true" @edit="edit">
      Headline
    </k-header>
  `
});

export const withOptions = () => ({
  template: `
    <k-header>
      Headline
      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>
    </k-header>
  `
});

export const bellsAndWhistles = () => ({
  template: `
    <k-header>
      Headline
      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>
      <k-prev-next slot="right" />
    </k-header>
  `
});

export const withTabs = () => ({
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
      Headline
      <k-button-group slot="left">
        <k-button icon="edit">Edit</k-button>
        <k-button icon="trash">Delete</k-button>
      </k-button-group>
      <k-prev-next slot="right" />
    </k-header>
  `,
});
