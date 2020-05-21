import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import items from "../../../storybook/data/Items.js"

export default {
  title: "UI | Data / Picker",
  decorators: [Padding]
};


export const single = () => ({
  data() {
    return {
      selected: []
    };
  },
  computed: {
    options() {
      return items(10, 1).map(item => {
        delete item.options;
        return item;
      });
    }
  },
  methods: {
    onSelect(selected) {
      action("select")(selected);
    }
  },
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        :options="options"
        v-model="selected"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const multiple = () => ({
  extends: single(),
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        :multiple="true"
        :options="options"
        v-model="selected"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const max = () => ({
  extends: single(),
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        :max="5"
        :multiple="true"
        :options="options"
        v-model="selected"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const cardlets = () => ({
  extends: single(),
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        v-model="selected"
        :multiple="true"
        :options="options"
        layout="cardlet"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const cards = () => ({
  extends: single(),
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        v-model="selected"
        :multiple="true"
        :options="options"
        layout="card"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const pagination = () => ({
  extends: single(),
  data() {
    return {
      selected: [],
      pagination: {
        page: 1,
        limit: 2,
        total: 20
      }
    };
  },
  computed: {
    options() {
      return items(
        this.pagination.limit,
        ((this.pagination.page - 1) * this.pagination.limit) + 1
      ).map(item => {
        delete item.options;
        return item;
      });
    }
  },
  methods: {
    onSelect(selected) {
      action("select")(selected);
    },
    onPaginate(pagination) {
      this.pagination.page = pagination.page;
      action("paginate")(pagination);
    }
  },
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        v-model="selected"
        :options="options"
        :multiple="true"
        :pagination="pagination"
        class="mb-8"
        @select="onSelect"
        @paginate="onPaginate"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const customToggle = () => ({
  extends: single(),
  template: `
    <div>
      <k-headline class="mb-3">Picker</k-headline>
      <k-picker
        v-model="selected"
        :multiple="true"
        :options="options"
        :toggle="(item, selected, max) => {
          if (selected) {
            return {
              icon: 'remove',
              color: 'green',
              tooltip: 'Remove'
            };
          } else {
            return {
              icon: 'add',
              tooltip: 'Add'
            };
          }
        }"
        class="mb-8"
        @select="onSelect"
      />
      <k-headline class="mb-3">Selected</k-headline>
      <k-code-block :code="selected" />
    </div>
  `
});

export const async = () => ({
  extends: single(),
  computed: {
    options() {
      return async () => {
        await new Promise(r => setTimeout(r, 1500));
        return items(10, 1).map(item => {
          delete item.options;
          return item;
        });
      };
    }
  },
});

export const asyncWithError = () => ({
  extends: single(),
  computed: {
    options() {
      return async () => {
        await new Promise(r => setTimeout(r, 1500));
        throw new Error("Something went wrong")
      };
    }
  },
});
