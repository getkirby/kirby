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
 * Preview for the `table` block
 *
 * @displayName BlockTypeTable
 * @internal
 */
export default {
  inheritAttrs: false,
  computed: {
    /**
     * Returns either explicitly defined columns
     * or fieldset fields as columns config
     * @returns {Object}
     */
    columns() {
      return this.table.columns || this.fields;
    },
    /**
     * @returns  {Object}
     */
    fields() {
      return this.table.fields || {};
    },
    /**
     * @returns {Array}
     */
    rows() {
      return this.content.rows || [];
    },
    /**
     * Returns table config from `rows`
     * field by looping through each tab
     * to find the field
     * @returns {Object}
     */
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
