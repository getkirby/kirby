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
    add() {
      return false;
    },
    delay() {
      return 0;
    },
    empty() {
      return null;
    },
    help() {
      return false;
    },
    image() {
      return {};
    },
    limit() {
      return 10;
    },
    layout() {
      return "list";
    },
    page() {
      return 1;
    },
    pages() {
      return async ({ page, limit }) => {

        await new Promise(r => setTimeout(r, this.delay));

        return {
          data: Pages(limit, ((page - 1) * limit) + 1),
          pagination: {
            total: 230
          }
        };
      };
    },
    sortable() {
      return false;
    },
  },
  methods: {
    onEmpty: action("empty"),
    onFlag: action("flag"),
    onOption: action("option")
  },
  template: `
    <k-pages-section
      :add="add"
      :empty="empty"
      :help="help"
      :image="image"
      :info="true"
      :layout="layout"
      :page="page"
      :pages="pages"
      :limit="limit"
      :sortable="sortable"
      label="Pages"
      @empty="onEmpty"
      @flag="onFlag"
      @option="onOption"
    />
  `
});

export const listWithImageSettings = () => ({
  extends: list(),
  computed: {
    image() {
      return {
        back: "pattern",
        cover: true,
        ratio: "3/2",
      };
    }
  }
});

export const listSortable = () => ({
  extends: list(),
  computed: {
    sortable() {
      return true;
    },
  }
});

export const listWithLimit = () => ({
  extends: list(),
  computed: {
    limit() {
      return 20;
    },
  }
});

export const listWithPage = () => ({
  extends: list(),
  computed: {
    page() {
      return 2;
    },
  }
});

export const listWithHelp = () => ({
  extends: list(),
  computed: {
    help() {
      return "Here's some help";
    },
  }
});

export const listWithError = () => ({
  extends: list(),
  computed: {
    pages() {
      return async ({ page, limit }) => {
        throw new Error("The pages could not be loaded");
      };
    },
  }
});

export const listWithSlowServer = () => ({
  extends: list(),
  computed: {
    delay() {
      return 2500;
    },
  }
});

export const listEmpty = () => ({
  extends: list(),
  computed: {
    pages() {
      return async () => {
        return [];
      };
    },
  }
});

export const listCustomEmpty = () => ({
  extends: list(),
  computed: {
    add() {
      return true;
    },
    empty() {
      return {
        icon: "draft",
        text: "No drafts yet",
      };
    },
    pages() {
      return async () => {
        return [];
      };
    },
  }
});

export const cardlets = () => ({
  extends: list(),
  computed: {
    layout() {
      return "cardlets";
    }
  }
});

export const cardletsWithImageSettings = () => ({
  extends: cardlets(),
  computed: {
    image() {
      return {
        back: "pattern",
        cover: true,
        ratio: "3/2",
      };
    }
  }
});

export const cardletsSortable = () => ({
  extends: cardlets(),
  computed: {
    sortable() {
      return true;
    },
  }
});

export const cardletsWithHelp = () => ({
  extends: cardlets(),
  computed: {
    help() {
      return "Here's some help";
    },
  }
});

export const cardletsWithError = () => ({
  extends: cardlets(),
  computed: {
    pages() {
      return async ({ page, limit }) => {
        throw new Error("The pages could not be loaded");
      };
    },
  }
});

export const cardletsWithSlowServer = () => ({
  extends: cardlets(),
  computed: {
    delay() {
      return 2500;
    },
  }
});

export const cardletsEmpty = () => ({
  extends: cardlets(),
  computed: {
    pages() {
      return async () => {
        return [];
      };
    },
  }
});

export const cardletsCustomEmpty = () => ({
  extends: cardlets(),
  computed: {
    add() {
      return true;
    },
    empty() {
      return {
        icon: "draft",
        text: "No drafts yet",
      };
    },
    pages() {
      return async () => {
        return [];
      };
    },
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

export const cardsWithImageSettings = () => ({
  extends: cards(),
  computed: {
    image() {
      return {
        back: "pattern",
        cover: true,
        ratio: "3/2",
      };
    }
  }
});

export const cardsSortable = () => ({
  extends: cards(),
  computed: {
    sortable() {
      return true;
    },
  }
});

export const cardsWithHelp = () => ({
  extends: cards(),
  computed: {
    help() {
      return "Here's some help";
    },
  }
});

export const cardsWithError = () => ({
  extends: cards(),
  computed: {
    pages() {
      return async ({ page, limit }) => {
        throw new Error("The pages could not be loaded");
      };
    },
  }
});

export const cardsWithSlowServer = () => ({
  extends: cards(),
  computed: {
    delay() {
      return 2500;
    },
    image() {
      return {
        back: "pattern",
        cover: true,
        ratio: "3/2",
      };
    }
  }
});

export const cardsEmpty = () => ({
  extends: cards(),
  computed: {
    pages() {
      return async () => {
        return [];
      };
    },
  }
});

export const cardsCustomEmpty = () => ({
  extends: cards(),
  computed: {
    add() {
      return true;
    },
    empty() {
      return {
        icon: "draft",
        text: "No drafts yet",
      };
    },
    pages() {
      return async () => {
        return [];
      };
    },
  }
});

