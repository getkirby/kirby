<template>
  <div
    :data-layout="layout"
    :data-loading="loading"
    class="k-collection"
  >
    <template v-if="loading">
      <slot name="loading">
        <k-empty-items
          v-bind="loader"
          :layout="layout"
          :limit="loader.limit || pagination.limit"
        />
      </slot>
    </template>
    <template v-else-if="items.length">
      <k-items
        :items="items"
        :layout="layout"
        :preview="preview"
        :size="size"
        :sortable="sortable"
        @flag="onFlag"
        @item="onItem"
        @option="onOption"
        @sort="onSort"
        @sortChange="onSortChange"
      />
    </template>
    <template v-else>
      <slot name="empty">
        <k-empty
          :layout="layout"
          :icon="emptyOptions.icon || 'page'"
          v-on="emptyListeners"
        >
          {{ emptyOptions.text || $t('items.empty') }}
        </k-empty>
      </slot>
    </template>
    <footer
      v-if="hasFooter"
      class="k-collection-footer flex justify-between"
    >
      <k-text
        v-if="help"
        theme="help"
        class="k-collection-help py-2 px-3 flex items-center"
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
import items from "@/ui/mixins/items.js";

export default {
  mixins: [items],
  props: {
    /**
     * Help text to be displayed below the collection in grey.
     */
    help: [Boolean, String],
    items: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    /**
     * Settings for the empty loading state.
     * See EmptyItems for available options
     */
    loader: {
      type: Object,
      default() {
        return {}
      }
    },
    /**
     * Enable/disable the loading state of the collection
     */
    loading: {
      type: Boolean,
      default: false,
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
    emptyListeners() {
      if (this.$listeners["empty"]) {
        return {
          click: this.$listeners["empty"]
        };
      }

      return {};
    },
    emptyOptions() {
      if (typeof this.empty === "string") {
        return {
          icon: "bars",
          text: this.empty
        };
      }

      return {
        icon: "bars",
        text: this.$t("items.empty"),
        ...this.empty
      };
    },
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
    onItem(item, itemIndex) {
      this.$emit("item", item, itemIndex);
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
.k-collection-footer {
  margin-right: -.75rem;
  margin-left: -.75rem;
}
.k-collection[data-loading] {
  cursor: wait;
}
.k-collection[data-loading] * {
  pointer-events: none;
}
</style>
