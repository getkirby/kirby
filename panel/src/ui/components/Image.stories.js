import Image from "./Image.vue";
import { withKnobs, text, select, boolean, number } from '@storybook/addon-knobs';

export default {
  title: "Media / Image",
  decorators: [withKnobs],
  component: Image
};

export const configurator = () => ({
  template: `
    <div :style="{ maxWidth: maxWidth + '%' }">
      <k-image v-bind="$props" />
    </div>
  `,
  props: {
    cover: {
      default: boolean("cover", false)
    },
    back: {
      default: select("back", ["none", "white", "black", "pattern"], "pattern")
    },
    maxWidth: {
      default: number("maxWidth", 50, {
        range: true,
        min: 5,
        max: 100
      })
    },
    src: {
      default: text("src", "https://source.unsplash.com/user/erondu/1600x900")
    },
    ratio: {
      default: text("ratio", "1/1")
    }
  }
});

