<template>
  <div
    :data-layout="layout"
    class="k-collection"
  >
    <k-items
      :items="items"
      :layout="layout"
      :sortable="sortable"
      @flag="onFlag"
      @option="onOption"
      @sort="onSort"
      @sortChange="onSortChange"
    />
    <footer
      v-if="hasFooter"
      class="k-collection-footer"
    >
      <k-text
        v-if="help"
        theme="help"
        class="k-collection-help"
        v-html="help"
      />
      <div class="k-collection-pagination">
        <k-pagination
          v-if="hasPagination"
          v-bind="paginationOptions"
          @paginate="onPaginate"
        />
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  props: {
    /**
     * Help text to be displayed below the collection in grey.
     */
    help: String,
    items: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Allow manual sorting via drag-and-drop
     */
    sortable: Boolean,
    pagination: {
      type: [Boolean, Object],
      default() {
        return false;
      }
    }
  },
  computed: {
    hasPagination() {
      if (this.pagination === false) {
        return false;
      }

      if (this.paginationOptions.hide === true) {
        return false;
      }

      if (this.pagination.total <= this.pagination.limit) {
        return false;
      }

      return true;
    },
    hasFooter() {
      if (this.hasPagination || this.help) {
        return true;
      }

      return false;
    },
    paginationOptions() {
      const options =
        typeof this.pagination !== "object" ? {} : this.pagination;
      return {
        limit: 10,
        details: true,
        keys: false,
        total: 0,
        hide: false,
        ...options
      };
    }
  },
  methods: {
    onFlag(item, itemIndex) {
      /**
       * The flag icon for an item has been clicked
       */
      this.$emit("flag", item, itemIndex);
    },
    onOption(option, item, itemIndex) {
      /**
       * Deprecated!
       */
      this.$emit("action", option, item, itemIndex);
      /**
       * The options icon for an item has been clicked
       */
      this.$emit("option", option, item, itemIndex);
    },
    onPaginate(pagination) {
      /**
       * Pagination has been altered/navigated
       */
      this.$emit("paginate", pagination);
    },
    onSort(items, event) {
      /**
       * Items have been re-sorted via drag-and-drop (Vue-draggable `end` event)
       */
      this.$emit("sort", items, event);
    },
    onSortChange(items, event) {
      /**
       * Items sorting has changed (Vue-draggable `change` event)
       */
      this.$emit("sortChange", items, event);
    }
  }
};
</script>

<style lang="scss">
.k-collection-help {
  padding: .5rem .75rem;
}
.k-collection-footer {
  display: flex;
  justify-content: space-between;
  margin-right: -.75rem;
  margin-left: -.75rem;
}
.k-collection-pagination {
  line-height: 1.25rem;
  min-height: 2.75rem;
}
.k-collection-pagination .k-pagination .k-button {
  padding: .5rem .75rem;
  line-height: 1.125rem;
}
</style>
