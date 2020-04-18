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
          :data-align="column.align"
          :style="'width:' + width(column.width)"
          class="k-table-column"
          @click="onHeader({
            label: column.label || columnIndex,
            column,
            columnIndex
          })"
        >
          <slot name="header" v-bind="{
            column,
            columnIndex,
            label: column.label || columnIndex,
          }">
            <p class="k-table-header-label">{{ column.label || columnIndex }}</p>
          </slot>
        </th>
        <th
          v-if="options"
          class="k-table-options-column"
        />
      </tr>
    </thead>

    <k-draggable
      :list="rows"
      :options="dragOptions"
      :handle="true"
      element="tbody"
      @end="onSort"
    >
      <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
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
          <slot name="cell" v-bind="{
            column,
            columnIndex,
            row,
            rowIndex,
            value: row[columnIndex],
          }">
            <component
              v-if="$helper.isComponent('table-' + (column.type || 'string') + '-cell')"
              :is="'k-table-' + column.type + '-cell'"
              :column="column"
              :value="row[columnIndex]"
            />
            <p v-else class="k-table-cell-value">
              {{ column.before }} {{ cellText(row[columnIndex], column.type, column) || "–" }} {{ column.after }}
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
  computed: {
    dragOptions() {
      return {
        disabled: !this.sortable,
        fallbackClass: "k-table-row-fallback",
        ghostClass: "k-table-row-ghost"
      };
    },
  },
  methods: {
    cellText(value, type, column) {
      if (typeof value === "object" && value !== null) {
        return "…";
      }

      return value.toString();
    },
    indexOf(index) {
      return this.index + index;
    },
    onCell(params) {
      this.$emit("cell", params);
    },
    onHeader(params) {
      this.$emit("header", params);
    },
    onOption(option, row, rowIndex) {
      this.$emit("option", option, row, rowIndex);
    },
    onSort() {
      this.$emit("sort", this.rows);
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
  font-size: $font-size-small;
  border-spacing: 0;
  border-collapse: collapse;
  box-shadow: $box-shadow-card;
  font-variant-numeric: tabular-nums;
}

/** Cells **/
.k-table th,
.k-table td {
  height: $table-row-height + 1;
  text-overflow: ellipsis;
  width: 100%;
  border-bottom: 1px solid $color-background;

  [dir="ltr"] & {
    border-right: 1px solid $color-background;
  }

  [dir="rtl"] & {
    border-left: 1px solid $color-background;
  }

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
  overflow: hidden;
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
  font-weight: 400;
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

/** Index column **/
.k-table th.k-table-index-column,
.k-table td.k-table-index-column {
  width: $table-row-height;
  text-align: center;
}
.k-table .k-table-index {
  font-size: $font-size-tiny;
  color: $color-light-grey;
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
  box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
  outline: 2px solid $color-focus;
  overflow: hidden;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}


</style>
