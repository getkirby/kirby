import Table from "./Table.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Data / Table",
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

export const stringCells = () => ({
  data() {
    return {
      rows: ["Paul", "Ringo", "", "George"].map(entry => {
        return {
          simple: entry,
          before: entry,
          after: entry,
          center: entry,
          right: entry
        }
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        simple: {
          label: "Simple"
        },
        before: {
          label: "Before",
          before: "Name:"
        },
        after: {
          label: "After",
          after: " (one of the Beatles)"
        },
        empty: {
          label: "Empty"
        },
        center: {
          label: "Center",
          align: "center"
        },
        right: {
          label: "Right",
          align: "right"
        },
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
  `
});

export const stringCellsWithWeirdTypes = () => ({
  data() {
    return {
      rows: [
        {
          array: ["Item 1", "Item 2", "Item 3"],
          object: {
            name: "Peter",
            email: "peter@getkirby.com"
          },
          number: 1,
          boolean: true,
          function: function () {

          }
        }
      ]
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        array: {
          label: "Array"
        },
        object: {
          label: "Object"
        },
        number: {
          label: "Number"
        },
        boolean: {
          label: "Boolean"
        },
        function: {
          label: "Function"
        },
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
  `
});

export const emailCells = () => ({
  data() {
    return {
      rows: ["paul@thebeatles.com", "ringo@thebeatles.com", "", "george@thebeatles.com"].map(entry => {
        return {
          simple: entry,
          before: entry,
          after: entry,
          center: entry,
          right: entry
        }
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        simple: {
          label: "Simple",
          type: "email",
        },
        before: {
          label: "Before",
          before: "Mail:",
          type: "email",
        },
        after: {
          label: "After",
          after: " (no-reply)",
          type: "email",
        },
        empty: {
          label: "Empty",
          type: "email",
        },
        center: {
          label: "Center",
          align: "center",
          type: "email",
        },
        right: {
          label: "Right",
          align: "right",
          type: "email",
        },
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
  `
});

export const urlCells = () => ({
  data() {
    return {
      rows: ["https://thebeatles.com/paul", "https://thebeatles.com/ringo", "", "https://thebeatles.com/george"].map(entry => {
        return {
          simple: entry,
          before: entry,
          after: entry,
          center: entry,
          right: entry
        }
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        simple: {
          label: "Simple",
          type: "url",
        },
        before: {
          label: "Before",
          before: "Link:",
          type: "url",
        },
        after: {
          label: "After",
          after: " (nofollow)",
          type: "url",
        },
        empty: {
          label: "Empty",
          type: "url",
        },
        center: {
          label: "Center",
          align: "center",
          type: "url",
        },
        right: {
          label: "Right",
          align: "right",
          type: "url",
        },
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
  `
});

export const numberCells = () => ({
  data() {
    return {
      rows: [12, 2984.1221, "", 0].map(entry => {
        return {
          simple: entry,
          decimal: entry,
          integer: entry,
          before: entry,
          after: entry,
          left: entry,
          center: entry,
        }
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        simple: {
          label: "Simple",
          type: "number",
        },
        decimal: {
          label: "Decimal",
          type: "number",
          precision: 2
        },
        integer: {
          label: "Integer",
          type: "number",
          precision: 0
        },
        before: {
          label: "Before",
          before: "€ ",
          type: "number",
          precision: 2
        },
        after: {
          label: "After",
          after: " entries",
          type: "number",
          precision: 0
        },
        empty: {
          label: "Empty",
          type: "number",
        },
        left: {
          label: "Left",
          align: "left",
          type: "number",
        },
        center: {
          label: "Center",
          align: "center",
          type: "number",
        },
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
  `
});

export const dateCells = () => ({
  data() {
    return {
      rows: ["2012-12-12 18:09:30", "", "2020-04-21 12:48:26", "2020-04-85"].map(entry => {
        return {
          date: entry,
          dateFormat: entry,
          dateTime: entry,
          timeOnly: entry,
          before: entry,
          after: entry,
          left: entry,
          center: entry,
        }
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        date: {
          label: "Date",
          type: "date",
        },
        dateFormat: {
          label: "Date Format",
          type: "date",
          date: "D.M.YYYY"
        },
        dateTime: {
          label: "Date & Time",
          type: "date",
          date: "DD.MM.YY",
          time: true
        },
        timeOnly: {
          label: "Time",
          type: "date",
          date: false,
          time: "HH:mm:ss"
        },
        before: {
          label: "Before",
          before: "Date: ",
          type: "date",
          date: "DD.MM.YY"
        },
        after: {
          label: "After",
          after: " o‘clock",
          type: "date",
          time: true,
          date: false
        },
        empty: {
          label: "Empty",
          type: "date",
        },
        left: {
          label: "Left",
          align: "left",
          type: "date",
        },
        center: {
          label: "Center",
          align: "center",
          type: "date",
        },
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
  `
});

export const imageCells = () => ({
  data() {
    return {
      rows: Array.from({ length: 10 }).map((entry, index) => {
        const url = "https://source.unsplash.com/user/erondu/160x90?" + index;

        return {
          simple: url,
          cover: url,
          back: url,
        };
      })
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        simple: {
          label: "Simple",
          type: "image",
        },
        cover: {
          label: "Cover",
          type: "image",
          cover: true,
        },
        back: {
          label: "Back",
          type: "image",
          back: "white",
        },
        empty: {
          label: "Empty",
          type: "image",
        },
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
  `
});


export const toggleCells = () => ({
  data() {
    return {
      rows: [
        {
          todo: "Wash hands",
          done: true,
        },
        {
          todo: "Go to party",
          done: false
        },
        {
          todo: "Stay at home",
          done: true,
        },
        {
          todo: "Listen to scientists",
          done: "1",
        },
        {
          todo: "Wear masks",
          done: 1,
        },
        {
          todo: "Read Twitter",
          done: 0
        }
      ]
    };
  },
  methods: {
    cell: action("cell"),
    header: action("header")
  },
  computed: {
    columns() {
      return {
        todo: {
          label: "Todo",
          type: "text",
        },
        done: {
          label: "Done",
          type: "toggle",
          width: "1/12"
        },
      };
    }
  },
  template: `
    <div>
      <k-headline class="mb-3">Table</k-headline>
      <k-table
        :columns="columns"
        :rows="rows"
        class="mb-8"
        @cell="cell"
        @header="header"
      />

      <k-headline class="mb-3">Rows</k-headline>
      <k-code-block :code="rows" />
    </div>
  `
});
