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
    empty() {
      return null;
    },
    help() {
      return false;
    },
    image() {
      return {};
    },
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
      :layout="layout"
      :pages="pages"
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

export const listWithHelp = () => ({
  extends: list(),
  computed: {
    help() {
      return "Here's some help";
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

export const cardletsWithHelp = () => ({
  extends: cardlets(),
  computed: {
    help() {
      return "Here's some help";
    },
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

export const cardsWithHelp = () => ({
  extends: cards(),
  computed: {
    help() {
      return "Here's some help";
    },
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

