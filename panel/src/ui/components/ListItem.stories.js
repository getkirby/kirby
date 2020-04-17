import ListItem from "./ListItem.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Data / List Item",
  component: ListItem,
  decorators: [Padding]
};

export const simple = () => ({
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      text="Card text"
    />
  `
});

export const info = () => ({
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      info="This is a nice card"
      text="Card text"
    />
  `
});

export const options = () => ({
  methods: {
    option: action("option")
  },
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      info="This is a nice card"
      text="Card text"
      @option="option"
    />
  `
});

export const link = () => ({
  methods: {
    option: action("option")
  },
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      info="This is a nice card"
      link="https://getkirby.com"
      text="Card text"
      @option="option"
    />
  `
});

export const imageRatio = () => ({
  methods: {
    option: action("option")
  },
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900',
        ratio: '1/1'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      info="This is a nice card"
      link="https://getkirby.com"
      text="Card text"
      @option="option"
    />
  `
});

export const imageBack = () => ({
  methods: {
    option: action("option")
  },
  template: `
    <k-list-item
      :image="{
        back: 'pattern',
        ratio: '1/1',
        url: 'https://source.unsplash.com/user/erondu/1600x900',
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      info="This is a nice card"
      link="https://getkirby.com"
      text="Card text"
      @option="option"
    />
  `
});

export const imageCover = () => ({
  methods: {
    option: action("option")
  },
  template: `
    <k-list-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900',
        ratio: '1/1',
        cover: true
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      info="This is a nice card"
      link="https://getkirby.com"
      text="Card text"
      @option="option"
    />
  `
});

