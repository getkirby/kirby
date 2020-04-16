import CardItems from "./CardItems.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Data / Card Items",
  component: CardItems,
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    items() {
      return [...Array(20).keys()].map(item => {
        return {
          text: "Card no. " + item,
          info: "Card info",
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
    <k-card-items :items="items" />
  `,
});



