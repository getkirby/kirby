<template>
  <k-collection
    :items="items"
    :layout="layout"
    :pagination="pagination"
    :sortable="sortable"
    @flag="onFlag"
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
     * Controll the toggle for each item with the
     * return value of the given function
     */
    toggle: Function,
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
  computed: {
    items() {
      return this.$helper.clone(this.options).map(item => {

        const selected = this.selected.includes(item.id);
        const max      = this.multiple && this.max && this.selected.length >= this.max;

        if (this.toggle) {
          item.flag = this.toggle(item, selected, max);
        } else {
          if (this.selected.includes(item.id)) {
            item.flag = {
              icon: this.multiple ? "check" : "circle-filled",
              tooltip: "Deselect",
              color: "green"
            };
          } else {
            item.flag = {
              icon: "circle-outline",
              tooltip: "Select",
              color: max ? "gray-light" : false,
              disabled: max
            };
          }
        }

        return item;
      });
    }
  },
  watch: {
    value() {
      this.selected = this.value;
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
    onFlag(item, itemIndex) {
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
      this.selected = items.map(item => item.id);
      this.$emit("input", this.selected);
    }
  }
};
</script>
