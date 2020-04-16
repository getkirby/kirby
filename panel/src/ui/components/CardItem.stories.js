import CardItem from "./CardItem.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Data / Card Item",
  component: CardItem,
  decorators: [Padding]
};

export const regular = () => ({
  props: {
    text: {
      default: "Card Text",
    },
    info: {
      default: "Card info",
    },
    link: {
      default: "https://getkirby.com"
    },
    image: {
      default() {
        return {
          url: "https://source.unsplash.com/user/erondu/1600x900",
        };
      }
    },
    options: {
      default() {
        return [
          { icon: "edit", text: "Edit" },
          { icon: "trash", text: "Delete" }
        ];
      }
    }
  },
  template: `
    <k-card-item :style="{ maxWidth: '20rem' }" v-bind="$props" />
  `,
});



