import { action } from "@storybook/addon-actions";
import PagesSection from "./PagesSection.vue";
import Padding from "../storybook/Padding.js";
import Pages from "../storybook/Pages.js";

export default {
  title: "UI | Blueprints / Pages Section",
  component: PagesSection,
  decorators: [Padding]
};

export const list = () => ({
  computed: {
    layout() {
      return "list";
    },
    pages() {
      return async ({ page, limit }) => {
        return {
          data: Pages(10, ((page - 1) * limit) + 1),
          pagination: {
            total: 230
          }
        };
      };
    }
  },
  methods: {
    onFlag: action("flag"),
    onOption: action("option")
  },
  template: `
    <k-pages-section
      :layout="layout"
      :pages="pages"
      label="Pages"
      @flag="onFlag"
      @option="onOption"
    />
  `
});

export const cardlets = () => ({
  extends: list(),
  computed: {
    layout() {
      return "cardlets";
    }
  }
});

export const cards = () => ({
  extends: list(),
  computed: {
    layout() {
      return "cards";
    }
  }
});

