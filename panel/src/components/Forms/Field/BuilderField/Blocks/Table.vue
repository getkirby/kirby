<template>
  <table class="k-block-table-preview" @click="$emit('open')">
    <tr>
      <th
        v-for="(column, columnName) in columns"
        :key="columnName"
        :data-align="column.align"
      >
        {{ column.label }}
      </th>
    </tr>
    <tr v-for="(row, rowIndex) in content.rows" :key="rowIndex">
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
export default {
  inheritAttrs: false,
  props: {
    content: Object,
    fieldset: Object
  },
  mounted() {
    if (this.content.rows.length === 0) {
      this.$emit("edit");
    }
  },
  computed: {
    columns() {
      return this.table.columns || this.table.fields;
    },
    table() {
      let table = null;

      Object.values(this.fieldset.tabs).forEach(tab => {
        if (tab.fields.rows) {
          table = tab.fields.rows;
        }
      });

      return table;
    }
  }
};
</script>

<style lang="scss">
.k-block-table {
  padding: 1.5rem 0;
}
.k-block-table-preview {
  cursor: pointer;
  width: 100%;
  border: 1px solid $color-gray-300;
  border-spacing: 0;
  border-radius: $rounded-sm;
  overflow: hidden;
  table-layout: fixed;
}
.k-block-table-preview td,
.k-block-table-preview th {
  text-align: left;
  line-height: 1.5em;
  padding: .5rem .75rem;
  font-size: $text-sm;
  border-bottom: 1px solid $color-gray-300;
}
.k-block-table-preview th {
  background: $color-gray-100;
  font-family: $font-mono;
  font-size: $text-xs;
}
.k-block-table-preview tr:last-child td {
  border-bottom: 0;
}
.k-block-table-preview [data-align="left"] {
  text-align: left;
}
.k-block-table-preview [data-align="right"] {
  text-align: right;
}
.k-block-table-preview [data-align="center"] {
  text-align: center;
}
</style>
