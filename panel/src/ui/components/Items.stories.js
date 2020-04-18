import Items from "./Items.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Data / Items",
  component: Items,
  decorators: [Padding]
};

export const list = () => ({
  data() {
    return {
      items: [...Array(20).keys()].map(item => {
        return {
          title: "List item no. " + item,
          info: "List item info",
          link: "https://getkirby.com",
          image: {
            url: "https://source.unsplash.com/user/erondu/1600x900"
          },
          options: [
            { icon: "edit", text: "Edit", click: "edit" },
            { icon: "trash", text: "Delete", click: "delete" }
          ]
        };
      })
    };
  },
  template: `
    <k-items
      :items="items"
      :sortable="true"
    />
  `
});

export const cardlets = () => ({
  extends: list(),
  template: `
    <k-items
      :items="items"
      :sortable="true"
      layout="cardlets"
    />
  `
});

export const cards = () => ({
  extends: list(),
  template: `
    <k-items
      :items="items"
      :sortable="true"
      layout="cards"
    />
  `
});
