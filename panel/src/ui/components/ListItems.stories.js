import ListItems from "./ListItems.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Data / List Items",
  component: ListItems,
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    items() {
      return [...Array(20).keys()].map(item => {
        return {
          text: "List item no. " + item,
          info: "List item info",
          link: "https://getkirby.com",
          image: {
            url: "https://source.unsplash.com/user/erondu/1600x900"
          },
          options: [
            { icon: "edit", text: "Edit" },
            { icon: "trash", text: "Delete" }
          ]
        };
      });
    }
  },
  template: `
    <k-list-items :items="items" />
  `,
});



