<template>
  <k-table
    :columns="columns"
    :empty="$t('field.structure.empty')"
    :rows="rows"
    class="k-block-type-table-preview"
    @dblclick.native="open"
  />
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
      return this.table.columns || this.fields;
    },
    fields() {
      return this.table.fields || {};
    },
    rows() {
      return this.content.rows || [];
    },
    table() {
      let table = null;

      for (const tab of Object.values(this.fieldset.tabs)) {
        if (tab.fields.rows) {
          table = tab.fields.rows;
        }
      }

      return table || {};
    }
  }
};
</script>

<style>
.k-block-type-table-preview {
  cursor: pointer;
  border: 1px solid var(--color-gray-300);
  border-spacing: 0;
  border-radius: var(--rounded-sm);
  overflow: hidden;
}
.k-block-type-table-preview td,
.k-block-type-table-preview th {
  text-align: start;
  line-height: 1.5em;
  font-size: var(--text-sm);
}
.k-block-type-table-preview th {
  padding: 0.5rem 0.75rem;
}
.k-block-type-table-preview td:not(.k-table-index-column) {
  padding: 0 0.75rem;
}
.k-block-type-table-preview td > *,
.k-block-type-table-preview td [class$="-field-preview"] {
  padding: 0;
}
</style>
