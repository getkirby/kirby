import Image from "./Image.vue";
import Padding from "../storybook/Padding.js";
import { withKnobs, text, select, boolean, number } from '@storybook/addon-knobs';

export default {
  title: "UI | Media / Image",
  decorators: [withKnobs, Padding],
  component: Image
};

export const simple = () => ({
  template: `
    <k-image
      ratio="16/9"
      src="https://source.unsplash.com/user/erondu/1600x900"
    />
  `,
});

export const backgrounds = () => ({
  template: `
    <k-grid gutter="large">
      <k-column width="1/4">
        <k-headline class="mb-3">black</k-headline>
        <k-image
          back="black"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">white</k-headline>
        <k-image
          back="white"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">pattern</k-headline>
        <k-image
          back="pattern"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">none</k-headline>
        <k-image
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
    </k-grid>
  `
});

export const ratios = () => ({
  template: `
    <k-grid gutter="large">
      <k-column width="1/4">
        <k-headline class="mb-3">4/5</k-headline>
        <k-image
          back="pattern"
          ratio="4/5"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">1/1</k-headline>
        <k-image
          back="pattern"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">3/2</k-headline>
        <k-image
          back="pattern"
          ratio="3/2"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">3/1</k-headline>
        <k-image
          ratio="3/1"
          back="pattern"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
    </k-grid>
  `
});

export const cover = () => ({
  template: `
    <k-grid gutter="large">
      <k-column width="1/4">
        <k-headline class="mb-3">4/5</k-headline>
        <k-image
          :cover="true"
          back="pattern"
          ratio="4/5"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">1/1</k-headline>
        <k-image
          :cover="true"
          back="pattern"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">3/2</k-headline>
        <k-image
          :cover="true"
          back="pattern"
          ratio="3/2"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
      <k-column width="1/4">
        <k-headline class="mb-3">3/1</k-headline>
        <k-image
          :cover="true"
          ratio="3/1"
          back="pattern"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-column>
    </k-grid>
  `
});



