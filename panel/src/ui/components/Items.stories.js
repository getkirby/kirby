import Items from "./Items.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

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
            url: "https://source.unsplash.com/user/erondu/1600x900?" + item
          },
          options: [
            { icon: "edit", text: "Edit", click: "edit" },
            { icon: "trash", text: "Delete", click: "delete" }
          ]
        };
      })
    };
  },
  methods: {
    onFlag: action("flag"),
    onOption: action("option"),
    onPaginate: action("paginate"),
    onSort: action("sort"),
    onSortChange: action("sortChange"),
  },
  template: `
    <k-items
      :items="items"
      :sortable="true"
      @flag="onFlag"
      @option="onOption"
      @paginate="onPaginate"
      @sort="onSort"
      @sortChange="onSortChange"
    />
  `
});

export const listWithImageSettings = () => ({
  extends: cards(),
  template: `
    <k-items
      :image="{
        ratio: '4/5',
        back: 'pattern',
      }"
      :items="items"
      :sortable="true"
      layout="list"
      @sort="onSort"
      @sortChange="onSortChange"
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
      @flag="onFlag"
      @option="onOption"
      @paginate="onPaginate"
      @sort="onSort"
      @sortChange="onSortChange"
    />
  `
});

export const cardletsWithImageSettings = () => ({
  extends: cards(),
  template: `
    <k-items
      :image="{
        ratio: '4/5',
        back: 'pattern',
      }"
      :items="items"
      :sortable="true"
      layout="cardlets"
      @sort="onSort"
      @sortChange="onSortChange"
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
      @sort="onSort"
      @sortChange="onSortChange"
    />
  `
});

export const cardsWithImageSettings = () => ({
  extends: cards(),
  template: `
    <k-items
      :image="{
        ratio: '4/5',
        back: 'pattern',
      }"
      :items="items"
      :sortable="true"
      layout="cards"
      @sort="onSort"
      @sortChange="onSortChange"
    />
  `
});
