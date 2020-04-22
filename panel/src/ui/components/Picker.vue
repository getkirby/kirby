<template>
  <k-collection
    :items="items"
    :layout="layout"
    :pagination="pagination"
    :sortable="sortable"
    @option="onOption"
    @paginate="onPaginate"
    @sort="onSort"
  />
</template>

<script>
export default {
  props: {
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Maximum number of selectable items in "multiple" mode
     */
    max: Number,
    /**
     * Allow selecting multiple items
     */
    multiple: {
      type: Boolean,
      default: false,
    },
    /**
     * Available options in the picker
     */
    options: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    /**
     * Pagination settings
     */
    pagination: {
      type: [Boolean, Object],
      default() {
        return false;
      }
    },
    /**
     * Enable drag & drop sorting for picker items
     */
    sortable: Boolean,
    /**
     * Array of selected items (array of ids)
     */
    value: Array
  },
  data() {
    return {
      selected: this.value
    };
  },
  watch: {
    value() {
      this.selected = this.value;
    }
  },
  computed: {
    items() {
      return this.$helper.clone(this.options).map(item => {

        if (this.selected.includes(item.id)) {
          item.options = [
            {
              icon: this.multiple ? "check" : "circle-filled",
              text: "Deselect",
              theme: "positive"
            }
          ];
        } else {
          item.options = [
            {
              icon: "circle-outline",
              text: "Select",
            }
          ];
        }

        return item;
      });
    }
  },
  methods: {
    onDeselect(id, item, itemIndex) {
      const index = this.selected.indexOf(id);

      if (index !== -1) {
        this.$delete(this.selected, index);
      }

      this.$emit("input", this.selected);
    },
    onOption(action, item, itemIndex) {
      if (this.selected.includes(item.id)) {
        this.onDeselect(item.id, item, itemIndex);
      } else {
        this.onSelect(item.id, item, itemIndex);
      }
    },
    onPaginate(pagination) {
      this.$emit("paginate", pagination);
    },
    onSelect(id, item, itemIndex) {
      if (this.multiple === false) {
        this.selected = [];
      }

      if (this.multiple && this.max && this.selected.length >= this.max) {
        // don't allow to add more items
        return;
      }

      this.selected.push(id);
      this.$emit("input", this.selected);
    },
    onSort(items, event) {
      this.$emit("sort", items, event);
    }
  }
};
</script>
