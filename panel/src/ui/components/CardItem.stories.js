import CardItem from "./CardItem.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Data / Card Item",
  component: CardItem,
  decorators: [Padding]
};

export const simple = () => ({
  template: `
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :style="{
        maxWidth: '20rem'
      }"
      text="Card text"
    />
  `
});

export const info = () => ({
  template: `
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :style="{
        maxWidth: '20rem'
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
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      :style="{
        maxWidth: '20rem'
      }"
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
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      :style="{
        maxWidth: '20rem'
      }"
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
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900',
        ratio: '1/1'
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      :style="{
        maxWidth: '20rem'
      }"
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
    <k-card-item
      :image="{
        back: 'pattern',
        ratio: '1/1',
        url: 'https://source.unsplash.com/user/erondu/1600x900',
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      :style="{
        maxWidth: '20rem'
      }"
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
    <k-card-item
      :image="{
        url: 'https://source.unsplash.com/user/erondu/1600x900',
        ratio: '1/1',
        cover: true
      }"
      :options="[
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]"
      :style="{
        maxWidth: '20rem'
      }"
      info="This is a nice card"
      link="https://getkirby.com"
      text="Card text"
      @option="option"
    />
  `
});

