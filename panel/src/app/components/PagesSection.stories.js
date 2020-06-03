import { action } from "@storybook/addon-actions";
import PagesSection from "./PagesSection.vue";
import Padding from "../../../storybook/theme/Padding.js";
import Pages from "../../../storybook/data/Pages.js";

export default {
  title: "App | Blueprints / Pages Section",
  decorators: [Padding]
};

const section = (delay = 0) => ({
  extends: PagesSection,
  computed: {
    pages() {
      return async ({ page, limit }) => {
        await new Promise(r => setTimeout(r, delay));

        return {
          data: Pages(limit, ((page - 1) * limit) + 1),
          pagination: {
            total: 230
          }
        };
      };
    }
  }
});

export const list = () => ({
  components: {
    "k-pages-section": section()
  },
  computed: {
    add() {
      return false;
    },
    empty() {
      return;
    },
    help() {
      return false;
    },
    icon() {
      return;
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
    preview() {
      return {};
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
      :info="true"
      :layout="layout"
      :page="page"
      :preview="preview"
      :limit="limit"
      :sortable="sortable"
      label="Pages"
      @empty="onEmpty"
      @flag="onFlag"
      @option="onOption"
    />
  `
});

export const listWithPreviewSettings = () => ({
  extends: list(),
  computed: {
    preview() {
      return {
        back: "pattern",
        cover: true,
        ratio: "3/2",
      };
    }
  }
});

export const listWithoutFigure = () => ({
  extends: list(),
  computed: {
    icon() {
      return false;
    },
    preview() {
      return false;
    },
    sortable() {
      return true;
    },
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
  components: {
    "k-pages-section": section(2500)
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

export const cardletsWithPreviewSettings = () => ({
  extends: cardlets(),
  computed: {
    preview() {
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
  components: {
    "k-pages-section": section(2500)
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

export const cardsWithPreviewSettings = () => ({
  extends: cards(),
  computed: {
    preview() {
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
  components: {
    "k-pages-section": section(2500)
  },
  computed: {
    preview() {
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
