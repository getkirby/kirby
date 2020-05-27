import SiteView from "./SiteView.vue";
import Pages from "../../../storybook/data/Pages.js";

export default {
  title: "App | Views / Site",
  component: SiteView
};

export const regular = () => ({
  data() {
    return {
      site: {
        title: "Maegazine",
        previewUrl: "https://getkirby.com"
      }
    };
  },
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
    isLocked() {
      return false;
    }
  },
  template: `
    <k-site-view
      :columns="columns"
      :is-locked="isLocked"
      :site="site"
    />
  `
});

export const locked = () => ({
  extends: regular(),
  computed: {
    isLocked() {
      return true;
    },
  },
});
