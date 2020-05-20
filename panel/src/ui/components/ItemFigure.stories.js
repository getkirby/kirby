import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Data / Item Figure",
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
          ratio: '16/9'
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
          type: 'user'
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
          color: 'green-light',
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
          color: 'green-light',
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
          color: 'green-light',
          size: 'large',
          type: 'image',
        }"
      />
    </div>
  `
});

export const customColorBack = () => ({
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :icon="{
          type: false,
          back: '#ff0000'
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
