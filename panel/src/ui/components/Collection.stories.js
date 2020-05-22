import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import items from "../../../storybook/data/Items.js";

export default {
  title: "UI | Data / Collection",
  decorators: [Padding]
};

export const list = () => ({
  data() {
    return {
      start: 1,
      pagination: {
        total: 230,
        limit: 10
      }
    };
  },
  computed: {
    items() {
      return items(10, this.start);
    }
  },
  methods: {
    onPaginate(pagination) {
      action("paginate")(pagination);
      this.start = pagination.start;
    }
  },
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
      @paginate="onPaginate"
    />
  `
});

export const listEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection @empty="onEmpty" />
  `
});

export const listCustomEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection
      :empty="{
        icon: 'add',
        text: 'Add the first item …'
      }"
      @empty="onEmpty"
    />
  `
});

export const listLoading = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loading="true"
      :pagination="{ total: 230 }"
    />
  `
});

export const listLoadingWithLimit = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ limit: 5 }"
      :loading="true"
      :pagination="{ total: 230 }"
    />
  `
});

export const listLoadingWithInfo = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ info: true }"
      :loading="true"
      :pagination="{ total: 230 }"
    />
  `
});

export const cardlets = () => ({
  data() {
    return {
      start: 1,
      pagination: {
        total: 230,
        limit: 10
      }
    };
  },
  computed: {
    items() {
      return items(10, this.start);
    }
  },
  methods: {
    onPaginate(pagination) {
      action("paginate")(pagination);
      this.start = pagination.start;
    }
  },
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
      layout="cardlet"
      @paginate="onPaginate"
    />
  `
});

export const cardletsEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection
      layout="cardlets"
      @empty="onEmpty"
    />
  `
});

export const cardletsCustomEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection
      :empty="{
        icon: 'add',
        text: 'Add the first item …'
      }"
      layout="cardlets"
      @empty="onEmpty"
    />
  `
});

export const cardletsLoading = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cardlets"
    />
  `
});

export const cardletsLoadingWithLimit = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ limit: 5 }"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cardlets"
    />
  `
});

export const cardletsLoadingWithInfo = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ info: true }"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cardlets"
    />
  `
});

export const cards = () => ({
  data() {
    return {
      start: 1,
      pagination: {
        total: 230,
        limit: 10
      }
    };
  },
  computed: {
    items() {
      return items(10, this.start);
    }
  },
  methods: {
    onPaginate(pagination) {
      action("paginate")(pagination);
      this.start = pagination.start;
    }
  },
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :sortable="true"
      layout="card"
      @paginate="onPaginate"
    />
  `
});

export const cardsWithPreviewSettings = () => ({
  data() {
    return {
      start: 1,
      pagination: {
        total: 230,
        limit: 10
      }
    };
  },
  computed: {
    items() {
      return items(10, this.start);
    }
  },
  methods: {
    onPaginate(pagination) {
      action("paginate")(pagination);
      this.start = pagination.start;
    }
  },
  template: `
    <k-collection
      :items="items"
      :pagination="pagination"
      :preview="{
        ratio: '3/2',
        back: 'pattern',
        cover: true
      }"
      :sortable="true"
      layout="card"
      @paginate="onPaginate"
    />
  `
});

export const cardsEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection
      layout="cards"
      @empty="onEmpty"
    />
  `
});

export const cardsCustomEmpty = () => ({
  methods: {
    onEmpty: action("empty")
  },
  template: `
    <k-collection
      :empty="{
        icon: 'add',
        text: 'Add the first item …'
      }"
      layout="cards"
      @empty="onEmpty"
    />
  `
});

export const cardsLoading = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cards"
    />
  `
});

export const cardsLoadingWithLimit = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ limit: 5 }"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cards"
    />
  `
});

export const cardsWithInfo = () => ({
  computed: {
    items() {
      return items(10);
    }
  },
  template: `
    <k-collection
      :items="items"
      :loader="{ info: true }"
      :loading="true"
      :pagination="{ total: 230 }"
      layout="cards"
    />
  `
});
