<template>
  <table
    class="k-table"
    :data-sortable="sortable"
    v-on="$listeners"
  >
    <thead>
      <tr>
        <th class="k-table-index-column">
          #
        </th>
        <th
          v-for="(column, columnIndex) in columns"
          :key="columnIndex + '-header'"
          :style="'width:' + width(column.width)"
          class="k-table-column"
          @click="onHeader({
            label: column.label || columnIndex,
            column,
            columnIndex
          })"
        >
          <slot
            name="header"
            v-bind="{
              column,
              columnIndex,
              label: column.label || columnIndex,
            }"
          >
            <p class="k-table-header-label">
              {{ column.label || columnIndex }}
            </p>
          </slot>
        </th>
        <th
          v-if="options"
          class="k-table-options-column"
        />
      </tr>
    </thead>

    <k-draggable
      :list="values"
      :options="dragOptions"
      :handle="true"
      element="tbody"
      @end="onSort"
    >
      <tr
        v-for="(row, rowIndex) in values"
        :key="rowIndex"
      >
        <td class="k-table-index-column">
          <k-sort-handle
            v-if="sortable"
            class="k-table-sort-handle"
          />
          <span class="k-table-index">{{ indexOf(rowIndex) }}</span>
        </td>
        <td
          v-for="(column, columnIndex) in columns"
          :key="rowIndex + '-' + columnIndex"
          :data-align="column.align"
          :style="'width:' + width(column.width)"
          :title="column.label"
          class="k-table-column"
          @click="onCell({
            value: row[columnIndex],
            row,
            rowIndex,
            column,
            columnIndex
          })"
        >
          <slot
            name="cell"
            v-bind="{
              column,
              columnIndex,
              row,
              rowIndex,
              value: row[columnIndex],
            }"
          >
            <component
              :is="'k-table-' + (column.type || 'text') + '-cell'"
              v-if="$helper.isComponent('table-' + (column.type || 'text') + '-cell')"
              :column="column"
              :value="row[columnIndex]"
              @input="onCellUpdate({
                value: $event,
                row,
                rowIndex,
                column,
                columnIndex
              })"
            />
            <p
              v-else
              class="k-table-cell-value"
            >
              {{ column.before }} {{ row[columnIndex] || "" }} {{ column.after }}
            </p>
          </slot>
        </td>
        <td
          v-if="options"
          class="k-table-options-column"
        >
          <k-options-dropdown
            :options="options"
            @option="onOption($event, row, rowIndex)"
          />
        </td>
      </tr>
    </k-draggable>
  </table>
</template>

<script>
export default {
  props: {
    columns: Object,
    index: {
      type: Number,
      default: 1
    },
    rows: Array,
    options: [Array, Function],
    sortable: Boolean
  },
  data() {
    return {
      values: this.rows
    };
  },
  computed: {
    dragOptions() {
      return {
        disabled: !this.sortable,
        fallbackClass: "k-table-row-fallback",
        ghostClass: "k-table-row-ghost"
      };
    },
  },
  watch: {
    rows() {
      this.values = this.rows;
    }
  },
  methods: {
    indexOf(index) {
      return this.index + index;
    },
    onCell(params) {
      this.$emit("cell", params);
    },
    onCellUpdate({ columnIndex, rowIndex, value }) {
      this.values[rowIndex][columnIndex] = value;
      this.$emit("input", this.values);
    },
    onHeader(params) {
      this.$emit("header", params);
    },
    onOption(option, row, rowIndex) {
      this.$emit("option", option, row, rowIndex);
    },
    onSort() {
      this.$emit("input", this.values);
      this.$emit("sort", this.values);
    },
    width(fraction) {
      if (!fraction) {
        return "auto";
      }
      const parts = fraction.toString().split("/");

      if (parts.length !== 2) {
        return "auto";
      }

      const a = Number(parts[0]);
      const b = Number(parts[1]);

      return parseFloat(100 / b * a, 2).toFixed(2) + "%";
    },
  }
};
</script>

<style lang="scss">
$table-row-height: 38px;

/** Table Layout **/
.k-table {
  position: relative;
  table-layout: fixed;
  width: 100%;
  background: $color-white;
  font-size: $text-sm;
  border-spacing: 0;
  box-shadow: $shadow;
  font-variant-numeric: tabular-nums;
  border-radius: $rounded-sm;
}

/** Cells **/
.k-table th,
.k-table td {
  height: $table-row-height + 1;
  text-overflow: ellipsis;
  width: 100%;
  border-bottom: 1px solid $color-background;
  line-height: 1.15em;

  [dir="ltr"] & {
    border-right: 1px solid $color-background;
  }

  [dir="rtl"] & {
    border-left: 1px solid $color-background;
  }

}
.k-table tr:first-child th:first-child {
  border-top-left-radius: $rounded-sm;
}
.k-table tr:first-child th:last-child {
  border-top-right-radius: $rounded-sm;
}

.k-table tr:last-child td {
  border-bottom: 0;
}
.k-table tbody tr:hover td {
  background: rgba($color-background, 0.25);
}

.k-table-header-label,
.k-table-cell-value {
  padding: 0 0.75rem;
  overflow-x: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Text aligment */
.k-table th.k-table-column[data-align="center"],
.k-table td.k-table-column[data-align="center"] {
  text-align: center;
}
.k-table th.k-table-column[data-align="right"],
.k-table td.k-table-column[data-align="right"] {
  [dir="ltr"] & {
    text-align: right;
  }

  [dir="rtl"] & {
    text-align: left;
  }
}

/** Sticky header **/
.k-table th {
  position: sticky;
  top: 0;
  right: 0;
  left: 0;
  width: 100%;
  font-weight: $font-normal;
  z-index: 1;
  color: $color-dark-grey;
  background: $color-white;

  [dir="ltr"] & {
    text-align: left;
  }

  [dir="rtl"] & {
    text-align: right;
  }

}
.k-table th::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  height: .5rem;
  background: -webkit-linear-gradient(top, rgba($color-black, .05), rgba($color-black, 0));
  z-index: 2;
}

/** Index column **/
.k-table th.k-table-index-column,
.k-table td.k-table-index-column {
  width: $table-row-height;
  text-align: center;
}
.k-table .k-table-index {
  font-size: $text-xs;
  color: $color-light-grey;
  line-height: 1.1em;
}

/** Options column **/
.k-table th.k-table-options-column,
.k-table td.k-table-options-column {
  width: $table-row-height !important;
}

/** Drag & Drop Sorting **/
.k-table-sort-handle {
  width: $table-row-height;
  height: $table-row-height;
  display: none;
}
.k-table[data-sortable] tr:hover .k-table-index {
  display: none;
}
.k-table[data-sortable] tr:hover .k-table-sort-handle {
  display: flex;
}
.k-table-row-fallback {
  opacity: 0 !important;
}
.k-table-row-ghost {
  background: $color-white;
  box-shadow: $shadow-xl;
  outline: 2px solid currentColor;
  overflow: hidden;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}

</style>
