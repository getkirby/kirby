import { action } from "@storybook/addon-actions";
import Files from "../../../storybook/data/Files.js";
import Padding from "../../../storybook/theme/Padding.js";
import FilesSection from "./FilesSection.vue";

export default {
  title: "App | Blueprints / Files Section",
  decorators: [Padding]
};

const section = (delay = 0) => ({
  extends: FilesSection,
  computed: {
    files() {
      return async ({ page, limit }) => {
        await new Promise(r => setTimeout(r, delay));

        return {
          data: Files(limit, ((page - 1) * limit) + 1),
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
    "k-files-section": section()
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
      return;
    },
    sortable() {
      return false;
    },
  },
  methods: {
    onFlag: action("flag"),
    onOption: action("option")
  },
  template: `
    <k-files-section
      :add="add"
      :empty="empty"
      :help="help"
      :info="true"
      :layout="layout"
      :page="page"
      :preview="preview"
      :limit="limit"
      :sortable="sortable"
      label="Files"
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
    files() {
      return async ({ page, limit }) => {
        throw new Error("The files could not be loaded");
      };
    },
  }
});

export const listWithSlowServer = () => ({
  extends: list(),
  components: {
    "k-files-section": section(2500)
  }
});

export const listEmpty = () => ({
  extends: list(),
  computed: {
    files() {
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
        icon: "heart",
        text: "No favorite images yet",
      };
    },
    files() {
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
    files() {
      return async ({ page, limit }) => {
        throw new Error("The files could not be loaded");
      };
    },
  }
});

export const cardletsWithSlowServer = () => ({
  extends: cardlets(),
  components: {
    "k-files-section": section(2500)
  }
});

export const cardletsEmpty = () => ({
  extends: cardlets(),
  computed: {
    files() {
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
        icon: "heart",
        text: "No favorite images yet",
      };
    },
    files() {
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
    files() {
      return async ({ page, limit }) => {
        throw new Error("The files could not be loaded");
      };
    },
  }
});

export const cardsWithSlowServer = () => ({
  extends: cards(),
  components: {
    "k-files-section": section(2500)
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
    files() {
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
        icon: "heart",
        text: "No favorite images yet",
      };
    },
    files() {
      return async () => {
        return [];
      };
    },
  }
});
