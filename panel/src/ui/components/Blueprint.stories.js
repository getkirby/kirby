import Sections from "./Sections.vue";
import Padding from "../storybook/Padding.js";
import Pages from "../storybook/Pages.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Blueprints / Sections",
  decorators: [Padding],
  component: Sections
};

export const pages = () => ({
  computed: {
    sections() {
      return {
        drafts: {
          label: "Drafts",
          type: "pages",
          add: true,
          pages: async () => {
            return Pages(2).map(page => {
              page.flag.icon.type  = "circle-outline";
              page.flag.icon.color = "red-light";
              return page;
            });
          }
        },
        published: {
          label: "Published",
          layout: "cards",
          type: "pages",
          image: {
            ratio: "3/2"
          },
          pages: async () => {
            return Pages(10);
          }
        }
      }
    },
  },
  template: `
    <k-sections :sections="sections" />
  `
});

