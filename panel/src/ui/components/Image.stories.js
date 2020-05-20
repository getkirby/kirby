import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Media / Imag",
  decorators: [Padding]
};

export const simple = () => ({
  template: `
    <k-image
      ratio="16/9"
      src="https://source.unsplash.com/user/erondu/1600x900"
    />
  `
});

export const backgrounds = () => ({
  computed: {
    colors() {
      return [
        "black",
        "white",
        "pattern",
        "red",
        "orange",
        "yellow",
        "green",
        "aqua",
        "blue",
        "purple",
        "#ff0000",
        "none"
      ];
    }
  },
  template: `
    <k-auto-grid style="--gap: 1.5rem; --min: 9rem">
      <div v-for="color in colors" :key="color">
        <k-headline class="mb-3">{{ color }}</k-headline>
        <k-image
          :back="color"
          ratio="1/1"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </div>
    </k-grid>
  `
});

export const ratios = () => ({
  computed: {
    ratios() {
      return [
        "4/5",
        "1/1",
        "3/2",
        "16/9",
        "3/1",
      ];
    }
  },
  template: `
    <k-auto-grid style="--gap: 1.5rem; --min: 9rem">
      <div v-for="ratio in ratios" :key="ratio">
        <k-headline class="mb-3">{{ ratio }}</k-headline>
        <k-image
          :ratio="ratio"
          back="pattern"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </div>
    </k-grid>
  `
});

export const cover = () => ({
  computed: {
    ratios() {
      return [
        "4/5",
        "1/1",
        "3/2",
        "16/9",
        "3/1",
      ];
    }
  },
  template: `
    <k-auto-grid style="--gap: 1.5rem; --min: 9rem">
      <div v-for="ratio in ratios" :key="ratio">
        <k-headline class="mb-3">{{ ratio }}</k-headline>
        <k-image
          :cover="true"
          :ratio="ratio"
          back="pattern"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </div>
    </k-grid>
  `
});
