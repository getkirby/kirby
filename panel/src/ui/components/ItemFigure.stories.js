import ItemFigure from "./ItemFigure.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Data / Item Figure",
  component: ItemFigure,
  decorators: [Padding]
};

export const image = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :image="{
          url: 'https://source.unsplash.com/user/erondu/1600x900'
        }"
      />
    </div>
  `
});

export const imageBack = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :image="{
          url: 'https://source.unsplash.com/user/erondu/1600x900',
          back: 'pattern'
        }"
      />
    </div>
  `
});

export const imageCover = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :image="{
          url: 'https://source.unsplash.com/user/erondu/1600x900',
          cover: true
        }"
      />
    </div>
  `
});

export const imageRatio = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :image="{
          url: 'https://source.unsplash.com/user/erondu/1600x900',
          cover: true,
          ratio: '4/5'
        }"
      />
    </div>
  `
});

export const icon = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="true"
      />
    </div>
  `
});

export const iconType = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="{
          type: 'image'
        }"
      />
    </div>
  `
});

export const iconColor = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="{
          color: 'var(--color-positive-light)',
          type: 'image',
        }"
      />
    </div>
  `
});

export const iconBack = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="{
          back: 'pattern',
          color: 'var(--color-positive-light)',
          type: 'image',
        }"
      />
    </div>
  `
});

export const iconSize = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="{
          back: 'pattern',
          color: 'var(--color-positive-light)',
          size: 'large',
          type: 'image',
        }"
      />
    </div>
  `
});

export const noImageSrc = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure />
    </div>
  `
});

export const noImageNoIcon = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="false"
        :image="false"
      />
    </div>
  `
});


