<template>
  <td :data-align="column.align">
    <template v-if="$helper.object.isEmpty(value) === false">
      <!-- Table cell type component -->
      <component
        :is="component"
        v-if="component !== false"
        :column="column"
        :field="field"
        :row="row"
        :value="value"
        @input="$emit('input', $event)"
      />
      <!-- Fallback to just text -->
      <p v-else class="k-table-cell-value">
        {{ column.before }}
        {{ text }}
        {{ column.after }}
      </p>
    </template>
  </td>
</template>

<script>
export default {
  props: {
    column: Object,
    field: Object,
    row: Object,
    value: {
      type: [String, Number, Object, Array],
      default: ""
    }
  },
  computed: {
    /**
     * Returns the component name (if exists) for
     * - field preview
     * - cell type
     * @returns {string|false}
     */
    component() {
      const type = this.column.type || this.field?.type || "text";

      if (this.$helper.isComponent(`k-${type}-field-preview`)) {
        return `k-${type}-field-preview`;
      }

      if (this.$helper.isComponent(`k-table-${type}-cell`)) {
        return `k-table-${type}-cell`;
      }

      return false;
    },
    /**
     * Takes a cell value and formats it based on column type
     * @param {Object} column
     * @param {mixed} value
     * @returns {string}
     */
    text() {
      let value = this.value;

      switch (this.column.type) {
        case "tags":
        case "multiselect":
          if (Array.isArray(value) === true) {
            value = value.map((item) => item.text || item);
          }

          return value.join(", ");
        case "checkboxes": {
          return value
            .map((item) => {
              let text = item;

              for (const option of this.column.options) {
                if (option.value === item) {
                  text = option.text;
                }
              }

              return text;
            })
            .join(", ");
        }
        case "radio":
        case "select": {
          const option = this.column.options.filter(
            (item) => item.value === value
          )[0];
          return option ? option.text : null;
        }
      }

      if (typeof value === "object" && value !== null) {
        return "…";
      }

      return value?.toString();
    }
  }
};
</script>

<style>
.k-table-cell-value {
  padding: 0 0.75rem;
  overflow-x: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
