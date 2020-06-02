import SiteView from "./SiteView.vue";
import Pages from "../../../storybook/data/Pages.js";

export default {
  title: "App | Views / Site",
  component: SiteView
};

export const regular = () => ({
  computed: {
    columns() {
      return [
        {
          width: "1/2",
          sections: {
            photography: {
              add: true,
              pages: async () => Pages(10),
              type: "pages",
              layout: "cards",
              preview: {
                ratio: "3/2",
                cover: true,
              },
            },
          },
        },
        {
          width: "1/2",
          sections: {
            notes: {
              add: true,
              pages: async () => Pages(7),
              type: "pages",
            },
            pages: {
              add: true,
              pages: async () => Pages(4),
              type: "pages",
            },
          },
        },
      ];
    },
    lock() {
      return false;
    },
    preview() {
      return "https://getkirby.com";
    },
    title() {
      return "Maegazine";
    }
  },
  template: `
    <k-site-view
      :columns="columns"
      :lock="lock"
      :rename="true"
      :preview="preview"
      :title="title"
    />
  `
});

export const locked = () => ({
  extends: regular(),
  computed: {
    lock() {
      return {
        email: "ada@getkirby.com",
      };
    },
  },
});
