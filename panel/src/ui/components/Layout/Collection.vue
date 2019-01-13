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

    <k-pagination
      v-if="pagination !== false && paginationOptions.hide !== true"
      v-bind="paginationOptions"
      @paginate="$emit('paginate', $event)"
    />
  </div>

</template>

<script>
export default {
  props: {
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
  data() {
    return {
      list: this.items
    };
  },
  computed: {
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
        align: "center",
        details: true,
        keys: false,
        total: 0,
        hide: false,
        ...options
      };
    }
  },
  watch: {
    items() {
      this.list = this.items;
    },
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
