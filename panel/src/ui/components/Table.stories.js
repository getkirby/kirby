import Table from "./Table.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Data / Table",
  component: Table,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      rows: [
        { name: "Paul", email: "paul@getkirby.com" },
        { name: "Ringo", email: "ringo@getkirby.com" },
        { name: "George", email: "george@getkirby.com" },
        { name: "John", email: "john@getkirby.com" }
      ]
    }
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        name: {
          label: "Name"
        },
        email: {
          label: "Email"
        }
      };
    }
  },
  template: `
    <k-table
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    />
  `,
});

export const columnWidth = () => ({
  ...regular(),
  computed: {
    columns() {
      return {
        name: {
          label: "Name",
          width: "1/4"
        },
        email: {
          label: "Email",
          width: "3/4"
        }
      };
    }
  },
  template: `
    <k-table
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    />
  `,
});

export const columnAlign = () => ({
  ...regular(),
  computed: {
    columns() {
      return {
        name: {
          label: "Name",
          align: "right"
        },
        email: {
          label: "Email",
          align: "center"
        }
      };
    }
  },
  template: `
    <k-table
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    />
  `,
});

export const sortable = () => ({
  ...regular(),
  methods: {
    ...regular().methods,
    sort: action("sort")
  },
  template: `
    <div>
      <k-headline class="mb-3">Table</k-headline>
      <k-table
        :columns="columns"
        :rows="rows"
        :sortable="true"
        class="mb-8"
        @cell="cell"
        @header="header"
        @sort="sort"
      />

      <k-headline class="mb-3">Rows</k-headline>
      <k-code-block :code="rows" />
    </div>
  `,
});

export const singleOption = () => ({
  ...regular(),
  computed: {
    columns() {
      return regular().computed.columns();
    },
    options() {
      return [
        { icon: "edit", text: "Edit", click: "edit" },
      ];
    }
  },
  methods: {
    ...sortable().methods,
    option: action("option"),
  },
  template: `
    <div>
      <k-headline class="mb-3">Table</k-headline>
      <k-table
        :columns="columns"
        :rows="rows"
        :options="options"
        :sortable="true"
        class="mb-8"
        @cell="cell"
        @header="header"
        @option="option"
        @sort="sort"
      />

      <k-headline class="mb-3">Rows</k-headline>
      <k-code-block :code="rows" />
    </div>
  `,
});

export const multipleOptions = () => ({
  ...regular(),
  computed: {
    columns() {
      return regular().computed.columns();
    },
    options() {
      return [
        { icon: "edit", text: "Edit", click: "edit" },
        { icon: "trash", text: "Delete", click: "remove" },
      ];
    }
  },
  methods: {
    ...singleOption().methods
  },
  template: `
    <div>
      <k-headline class="mb-3">Table</k-headline>
      <k-table
        :columns="columns"
        :rows="rows"
        :options="options"
        :sortable="true"
        class="mb-8"
        @cell="cell"
        @header="header"
        @option="option"
        @sort="sort"
      />

      <k-headline class="mb-3">Rows</k-headline>
      <k-code-block :code="rows" />
    </div>
  `,
});


export const customIndex = () => ({
  ...regular(),
  template: `
    <k-table
      :index="12"
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    />
  `,
});


export const cellSlot = () => ({
  ...regular(),
  template: `
    <k-table
      :index="12"
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    >
      <template #cell="{ column, columnIndex, row, rowId, value }">
        <p class="k-table-cell-value">
          <template v-if="columnIndex === 'email'">
            <a :href="'mailto:' + value" class="underline">{{ value }}</a>
          </template>
          <template v-else>
            {{ value }}
          </template>
        </p>
      </template>
    </k-table>
  `,
});

export const headerSlot = () => ({
  ...regular(),
  template: `
    <k-table
      :index="12"
      :columns="columns"
      :rows="rows"
      @cell="cell"
      @header="header"
    >
      <template #header="{ label }">
        <span class="flex items-center justify-between">
          <span class="px-3">{{ label }}</span>
          <k-options-dropdown
            :options="[
              { icon: 'edit', text: 'Rename column' },
              '-',
              { icon: 'angle-left', text: 'Insert left' },
              { icon: 'angle-right', text: 'Insert right' },
              '-',
              { icon: 'trash', text: 'Delete column' },
            ]"
            icon="angle-down"
          />
        </span>
      </template>
    </k-table>
  `,
});

