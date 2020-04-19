import Collection from "./Collection.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Data / Collection",
  component: Collection,
  decorators: [Padding]
};

export const list = () => ({
  data() {
    return {
      items: [...Array(10).keys()].map(item => {
        return {
          title: "List item no. " + item,
          info: "List item info",
          link: "https://getkirby.com",
          image: {
            url: "https://source.unsplash.com/user/erondu/1600x900?" + item
          },
          options: [
            { icon: "edit", text: "Edit", click: "edit" },
            { icon: "trash", text: "Delete", click: "delete" }
          ]
        };
      }),
      pagination: {
        total: 230,
        limit: 10
      }
    };
  },
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
    />
  `
});

export const cardlets = () => ({
  extends: list(),
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
      layout="cardlets"
    />
  `
});

export const cards = () => ({
  extends: list(),
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
      layout="cards"
    />
  `
});
