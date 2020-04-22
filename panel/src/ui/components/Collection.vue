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
      this.$emit("flag", item, itemIndex);
    },
    onOption(option, item, itemIndex) {
      // deprecated
      this.$emit("action", option, item, itemIndex);
      this.$emit("option", option, item, itemIndex);
    },
    onPaginate(pagination) {
      this.$emit("paginate", pagination);
    },
    onSort(items, event) {
      this.$emit("sort", items, event);
    },
    onSortChange(items, event) {
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
