<template>
  <div :data-layout="layout" class="k-collection">
    <k-draggable
      :list="items"
      :options="dragOptions"
      :element="elements.list"
      :data-size="size"
      :handle="true"
      @change="$emit('change', $event)"
      @end="onEnd"
    >
      <!--
        Emitted on every dropdown action / option
        @event action
        @property {object} item
        @property {string} click
      -->
      <component
        :is="elements.item"
        v-for="(item, index) in items"
        :key="index"
        :class="{'k-draggable-item': item.sortable}"
        v-bind="item"
        @action="$emit('action', item, $event)"
        @dragstart="onDragStart($event, item.dragText)"
      />
    </k-draggable>

    <footer v-if="hasFooter" class="k-collection-footer">
      <!-- eslint-disable vue/no-v-html -->
      <k-text
        v-if="help"
        theme="help"
        class="k-collection-help"
        v-html="help"
      />
      <!-- eslint-enable vue/no-v-html -->
      <div class="k-collection-pagination">
        <!--
          Emitted when the pagination changes
          @event paginate
          @property {object} pagination
        -->
        <k-pagination
          v-if="hasPagination"
          v-bind="paginationOptions"
          @paginate="$emit('paginate', $event)"
        />
      </div>
    </footer>
  </div>
</template>

<script>
/**
 * The `k-collection` component is a wrapper around `k-cards` and `k-list-items` that makes it easy to switch between the two layouts and adds sortabilty and pagination to the items.
 */
export default {
  props: {
    /**
     * Help text to show below the collection
     */
    help: String,
    items: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    /**
     * Layout of the collection
     * @values list, cards
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Size for items in cards layout
     * @values tiny, small, medium, large, huge
     */
    size: String,
     /**
      * Whether the collection can be sorted
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
    dragOptions() {
      return {
        sort: this.sortable,
        disabled: this.sortable === false,
        draggable: ".k-draggable-item"
      };
    },
    elements() {
      const layouts = {
        cards: {
          list: "k-cards",
          item: "k-card"
        },
        list: {
          list: "k-list",
          item: "k-list-item"
        }
      };

      if (layouts[this.layout]) {
        return layouts[this.layout];
      }

      return layouts["list"];
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
  watch: {
    $props() {
      this.$forceUpdate();
    }
  },
  over: null,
  methods: {
    onEnd() {
      if (this.over) {
        this.over.removeAttribute("data-over");
      }
      /**
       * Emitted when the sorting has stopped
       * @event sort
       * @property {array} items
       */
      this.$emit("sort", this.items);
    },
    onDragStart($event, dragText) {
      this.$store.dispatch("drag", {
        type: "text",
        data: dragText
      });
    }
  }
};
</script>

<style>
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
  flex-shrink: 0;
  min-height: 2.75rem;
}
.k-collection-pagination .k-pagination .k-button {
  padding: .5rem .75rem;
  line-height: 1.125rem;
}
</style>
