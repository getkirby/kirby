<template>
  <table
    class="k-table"
    :data-disabled="disabled"
    :data-indexed="hasIndexColumn"
  >
    <!-- Header row -->
    <thead>
      <tr>
        <th v-if="hasIndexColumn" class="k-table-index-column">#</th>

        <template v-for="(column, columnIndex) in columns">
          <th
            :key="columnIndex + '-header'"
            :style="'width:' + width(column.width)"
            class="k-table-column"
            @click="
              onHeader({
                column,
                columnIndex
              })
            "
          >
            <slot
              name="header"
              v-bind="{
                column,
                columnIndex,
                label: label(column, columnIndex)
              }"
            >
              {{ label(column, columnIndex) }}
            </slot>
          </th>
        </template>

        <th v-if="hasOptions" class="k-table-options-column"></th>
      </tr>
    </thead>

    <k-draggable
      :list="values"
      :options="dragOptions"
      :handle="true"
      element="tbody"
      @end="onSort"
    >
      <!-- Empty -->
      <tr v-if="rows.length === 0">
        <td :colspan="columnsCount" class="k-table-empty">
          {{ empty }}
        </td>
      </tr>

      <!-- Rows -->
      <tr v-for="(row, rowIndex) in values" v-else :key="rowIndex">
        <!-- Index & drag handle -->
        <td
          v-if="hasIndexColumn"
          :data-sortable="sortable && row.sortable !== false"
          class="k-table-index-column"
        >
          <slot
            name="index"
            v-bind="{
              row,
              rowIndex
            }"
          >
            <div class="k-table-index" v-text="index + rowIndex" />
          </slot>

          <k-sort-handle
            v-if="sortable && row.sortable !== false"
            class="k-table-sort-handle"
          />
        </td>

        <!-- Cell -->
        <template v-for="(column, columnIndex) in columns">
          <k-table-cell
            :key="rowIndex + '-' + columnIndex"
            :column="column"
            :field="fields[columnIndex]"
            :row="row"
            :value="row[columnIndex]"
            :style="'width:' + width(column.width)"
            class="k-table-column"
            @click.native="
              onCell({
                row,
                rowIndex,
                column,
                columnIndex
              })
            "
            @input="
              onCellUpdate({
                columnIndex,
                rowIndex,
                value: $event
              })
            "
          />
        </template>

        <!-- Options -->
        <td v-if="hasOptions" class="k-table-options-column">
          <slot name="options" v-bind="{ row, rowIndex, options }">
            <k-options-dropdown
              :options="row.options || options"
              @option="onOption($event, row, rowIndex)"
            />
          </slot>
        </td>
      </tr>
    </k-draggable>
  </table>
</template>

<script>
/**
 * A simple table component with columns and rows
 *
 * Events:
 * cell, input, header, option, sort
 */
export default {
  props: {
    /**
     * Configuration which columns to include.
     * Supported keys: after, before, label, type, width
     * @example { title: { label: "title", type: "text" } }
     */
    columns: Object,
    /**
     * Whether table is disabled
     */
    disabled: Boolean,
    /**
     * Optional fields configuration that match columns
     */
    fields: {
      type: Object,
      default: () => ({})
    },
    /**
     * Text to be shown when table has no rows
     */
    empty: String,
    /**
     * Index number for first column
     */
    index: {
      type: [Number, Boolean],
      default: 1
    },
    /**
     * Array of table rows
     */
    rows: Array,
    /**
     * What options to include in dropdown
     */
    options: [Array, Function],
    /**
     * Whether table is sortable
     */
    sortable: Boolean
  },
  data() {
    return {
      values: this.rows
    };
  },
  computed: {
    columnsCount() {
      return Object.keys(this.columns).length;
    },
    dragOptions() {
      return {
        disabled: !this.sortable,
        fallbackClass: "k-table-row-fallback",
        ghostClass: "k-table-row-ghost"
      };
    },
    hasIndexColumn() {
      return this.sortable || this.index !== false;
    },
    hasOptions() {
      return (
        this.options ||
        Object.values(this.rows).filter((row) => row.options).length > 0
      );
    }
  },
  watch: {
    rows() {
      this.values = this.rows;
    }
  },
  methods: {
    isColumnEmpty(columnIndex) {
      return (
        this.rows.filter(
          (row) => this.$helper.object.isEmpty(row[columnIndex]) === false
        ).length === 0
      );
    },
    label(column, columnIndex) {
      return column.label || this.$helper.string.ucfirst(columnIndex);
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
    onSort(e) {
      console.log(e);
      this.$emit("input", this.values);
      this.$emit("sort", this.values);
    },
    width(fraction) {
      return fraction ? this.$helper.ratio(fraction, "auto", false) : "auto";
    }
  }
};
</script>

<style>
/** Table Layout **/
.k-table {
  --table-row-height: 38px;
  position: relative;
  table-layout: fixed;
  width: 100%;
  background: var(--color-white);
  font-size: var(--text-sm);
  border-spacing: 0;
  box-shadow: var(--shadow);
  font-variant-numeric: tabular-nums;
}

/** Cells **/
.k-table th,
.k-table td {
  height: var(--table-row-height);
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  border-inline-end: 1px solid var(--color-background);
  line-height: 1.25em;
}

.k-table th:last-child,
.k-table td:last-child {
  height: var(--table-row-height);
  border-inline-end: 0;
}

.k-table th,
.k-table tr:not(:last-child) td {
  border-bottom: 1px solid var(--color-background);
}

.k-table td:last-child {
  overflow: visible;
}

[dir="ltr"] .k-table th,
[dir="ltr"] .k-table td {
  border-right: 1px solid var(--color-background);
}

[dir="rtl"] .k-table th,
[dir="rtl"] .k-table td {
  border-left: 1px solid var(--color-background);
}

.k-table tbody tr:hover td {
  background: rgba(239, 239, 239, 0.25);
}

/* Text aligment */
.k-table-column[data-align] {
  text-align: var(--align);
}
.k-table-column[data-align="right"] > .k-input {
  flex-direction: column;
  align-items: flex-end;
}

/** Sticky header **/
.k-table th {
  position: sticky;
  top: 0;
  inset-inline: 0;
  width: 100%;
  padding: 0 0.75rem;
  z-index: 1;
  font-family: var(--font-mono);
  font-size: var(--text-xs);
  font-weight: 400;
  color: var(--color-gray-600);
  background: var(--color-gray-100);
  text-align: start;
}
[dir="ltr"] .k-table th {
  text-align: left;
}

[dir="rtl"] .k-table th {
  text-align: right;
}
.k-table th::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  height: 0.5rem;
  background: -webkit-linear-gradient(top, rgba(#000, 0.05), rgba(#000, 0));
  z-index: 2;
}

.k-table-index,
.k-table .k-sort-handle {
  display: flex;
  justify-content: center;
  align-items: center;
  width: var(--table-row-height);
  height: var(--table-row-height);
}

/** Sort handle */
.k-table .k-sort-handle,
.k-table tr:hover .k-table-index-column[data-sortable="true"] .k-table-index {
  display: none;
}
.k-table tr:hover .k-sort-handle {
  display: flex !important;
}

.k-table-row-ghost {
  background: var(--color-white);
  box-shadow: rgba(17, 17, 17, 0.25) 0 5px 10px;
  outline: 2px solid var(--color-focus);
  margin-bottom: 2px;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}

.k-table-row-fallback {
  opacity: 0 !important;
}

/** Index column **/
th.k-table-index-column,
td.k-table-index-column {
  width: var(--table-row-height);
  text-align: center;
}
.k-table-index {
  font-size: var(--text-xs);
  color: var(--color-gray-500);
  line-height: 1.1em;
}

/** Options column **/
th.k-table-options-column,
td.k-table-options-column {
  width: var(--table-row-height);
}

/** Empty */
.k-table-empty {
  color: var(--color-gray-600);
  font-size: var(--text-sm);
}

/** Disabled */
[data-disabled="true"] .k-table {
  background: var(--color-background);
}
[data-disabled="true"] .k-table th,
[data-disabled="true"] .k-table td {
  background: var(--color-background);
  border-bottom: 1px solid var(--color-border);
  border-inline-end: 1px solid var(--color-border);
}
[data-disabled="true"] .k-table td:last-child {
  overflow: hidden;
  text-overflow: ellipsis;
}

/** Mobile */
@media screen and (max-width: 65em) {
  .k-table td,
  .k-table th {
    display: none;
  }

  .k-table th:first-child,
  .k-table[data-indexed="true"] th:nth-child(2),
  .k-table th:last-child,
  .k-table td:first-child,
  .k-table[data-indexed="true"] td:nth-child(2),
  .k-table td:last-child {
    display: table-cell;
  }

  .k-table th.k-table-column:nth-child(2),
  .k-table td.k-table-column:nth-child(2) {
    width: auto !important;
  }
}
</style>
