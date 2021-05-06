<template>
  <table class="k-block-type-table-preview" @dblclick="open">
    <tr>
      <th
        v-for="(column, columnName) in columns"
        :key="columnName"
        :data-align="column.align"
      >
        {{ column.label }}
      </th>
    </tr>
    <tr v-if="rows.length === 0">
      <td :colspan="columnsCount">
        <small class="k-block-type-table-preview-empty">{{ $t('field.structure.empty') }}</small>
      </td>
    </tr>
    <tr v-for="(row, rowIndex) in rows" v-else :key="rowIndex">
      <td
        v-for="(column, columnName) in columns"
        :key="rowIndex + '-' + columnName"
        :data-align="column.align"
      >
        {{ column.before }} {{ row[columnName] }} {{ column.after }}
      </td>
    </tr>
  </table>
</template>

<script>
/**
 * @displayName BlockTypeTable
 * @internal
 */
export default {
  inheritAttrs: false,
  computed: {
    columns() {
      return this.table.columns || this.table.fields || {};
    },
    columnsCount() {
      return Object.keys(this.columns).length;
    },
    rows() {
      return this.content.rows || [];
    },
    table() {
      let table = null;
      Object.values(this.fieldset.tabs).forEach(tab => {
        if (tab.fields.rows) {
          table = tab.fields.rows;
        }
      });

      return table || {};
    }
  }
};
</script>

<style>
.k-block-type-table-preview {
  cursor: pointer;
  width: 100%;
  border: 1px solid var(--color-gray-300);
  border-spacing: 0;
  border-radius: var(--rounded-sm);
  overflow: hidden;
  table-layout: fixed;
}
.k-block-type-table-preview td,
.k-block-type-table-preview th {
  text-align: left;
  line-height: 1.5em;
  padding: .5rem .75rem;
  font-size: var(--text-sm);
  border-bottom: 1px solid var(--color-gray-300);
}
.k-block-type-table-preview th {
  background: var(--color-gray-100);
  font-family: var(--font-mono);
  font-size: var(--text-xs);
}
.k-block-type-table-preview tr:last-child td {
  border-bottom: 0;
}
.k-block-type-table-preview [data-align="left"] {
  text-align: left;
}
.k-block-type-table-preview [data-align="right"] {
  text-align: right;
}
.k-block-type-table-preview [data-align="center"] {
  text-align: center;
}
.k-block-type-table-preview-empty {
  color: var(--color-gray-600);
  font-size: var(--text-sm);
}
</style>
