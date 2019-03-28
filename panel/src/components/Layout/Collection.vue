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
      <component
        v-for="(item, index) in items"
        :is="elements.item"
        :class="{'k-draggable-item': item.sortable}"
        :key="index"
        v-bind="item"
        @action="$emit('action', item, $event)"
        @dragstart="onDragStart($event, item.dragText)"
      />
    </k-draggable>

    <footer v-if="hasFooter" class="k-collection-footer">
      <div class="k-collection-pagination">
        <k-pagination
          v-if="hasPagination"
          v-bind="paginationOptions"
          @paginate="$emit('paginate', $event)"
        />
      </div>
      <k-text
        v-if="help"
        theme="help"
        class="k-collection-help"
        v-html="help"
      />
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
    layout: {
      type: String,
      default: "list"
    },
    size: String,
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

<style lang="scss">
.k-collection-help {
  padding: .75rem;
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
  padding: .75rem;
  line-height: 1.125rem;
}
</style>
