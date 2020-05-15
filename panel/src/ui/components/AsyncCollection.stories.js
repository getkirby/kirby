import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";
import items from "../../../storybook/data/Items.js";

export default {
  title: "UI | Data / Async Collection",
  decorators: [Padding]
};

export const list = () => ({
  computed: {
    delay() {
      return 0;
    },
    help() {
      return null;
    },
    items() {
      // this would normally be the place where
      // you make API requests and return the results
      return async ({ page, limit }) => {

        await new Promise(r => setTimeout(r, this.delay));

        return {
          data: items(limit, ((page - 1) * limit) + 1),
          pagination: {
            total: 230
          }
        };

      };
    },
    layout() {
      return "list";
    },
  },
  methods: {
    onFlag: action("flag"),
    onOption: action("option"),
    onPaginate: action("paginate"),
    onSort: action("sort"),
    onSortChange: action("sortChange"),
  },
  template: `
    <div>
      <k-async-collection
        ref="collection"
        :help="help"
        :items="items"
        :layout="layout"
        :loader="{
          info: true
        }"
        class="mb-6"
        @flag="onFlag"
        @option="onOption"
        @paginate="onPaginate"
        @sort="onSort"
        @sortChange="onSortChange"
      />

      <hr class="mb-3">
      <k-button @click="$refs.collection.reload()" icon="refresh">Reload</k-button>
    </div>
  `,
});

export const listWithSlowServer = () => ({
  extends: list(),
  computed: {
    delay() {
      return 2000;
    },
    layout() {
      return "list";
    },
  }
});

export const listWithHelp = () => ({
  extends: list(),
  computed: {
    layout() {
      return "list";
    },
    help() {
      return "Those are some really nice items."
    }
  }
});

export const cardlets = () => ({
  extends: list(),
  computed: {
    layout() {
      return "cardlet";
    }
  }
});

export const cardletsWithSlowServer = () => ({
  extends: list(),
  computed: {
    delay() {
      return 2000;
    },
    layout() {
      return "cardlet";
    },
  }
});

export const cardletsWithHelp = () => ({
  extends: list(),
  computed: {
    layout() {
      return "cardlet";
    },
    help() {
      return "Those are some really nice items."
    }
  }
});

export const cards = () => ({
  extends: list(),
  computed: {
    layout() {
      return "card";
    }
  }
});

export const cardsWithHelp = () => ({
  extends: list(),
  computed: {
    layout() {
      return "card";
    },
    help() {
      return "Those are some really nice items."
    }
  }
});

export const cardsWithSlowServer = () => ({
  extends: list(),
  computed: {
    delay() {
      return 2000;
    },
    layout() {
      return "cards";
    },
  }
});

export const cardsWithErrorAndDelay = () => ({
  extends: list(),
  computed: {
    delay() {
      return 2000;
    },
    items() {
      return async () => {
        await new Promise(r => setTimeout(r, this.delay));
        throw new Error("Something went wrong");
      };
    },
    layout() {
      return "cards";
    },
  }
});
