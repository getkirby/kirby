import ListItem from "./ListItem.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Data / List Item",
  component: ListItem,
  decorators: [Padding]
};

export const regular = () => ({
  props: {
    text: {
      default: "Item Text",
    },
    info: {
      default: "Item info",
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
    <k-list-item v-bind="$props" />
  `,
});



