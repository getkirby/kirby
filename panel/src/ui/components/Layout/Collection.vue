<template>
  <div :data-layout="layout" class="k-collection">
    <k-draggable
      :list="items"
      :options="dragOptions"
      :element="elements.list"
      @start="onStart"
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
      default: "list",
    },
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
    }
  },
  computed: {
    dragOptions() {
      return {
        sort: this.sortable,
        forceFallback: true,
        fallbackClass: "sortable-fallback",
        filter: ".disabled",
        delay: 1,
        disabled: this.sortable === false,
        draggable: ".k-draggable-item",
        handle: ".k-sort-handle",
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
        },
      };

      if (layouts[this.layout]) {
        return layouts[this.layout];
      }

      return layouts["list"];
    },
    paginationOptions() {
      const options = (typeof this.pagination !== "object") ? {} : this.pagination;
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
      this.$store.dispatch("drag", null);
    },
    onDragStart($event, dragText) {
      this.$store.dispatch("drag", {
        type: "text",
        data: dragText
      });
    },
    onStart() {
      this.$store.dispatch("drag", {
        type: "item"
      });
    }
  }
}
</script>

<style lang="scss">

.k-collection > *:not(:empty) {
  min-height: 38px;
  margin-bottom: 2px;
}
.k-collection > *:empty {
  position: relative;
}

.k-collection > *:empty:after {
  background: lighten($color-light-grey, 27.5%);
  border-radius: $border-radius;
  padding: .375rem .75rem;
  line-height: 1.25rem;
  border-left: 2px solid $color-light-grey;
  padding: .5rem 1.5rem;
  word-wrap: break-word;
  font-size: $font-size-small;
  content: "\00A0";
  display: block;
}

.k-collection .sortable-ghost {
  position: relative;
  outline: 2px solid $color-focus;
  z-index: 1;
  box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
}
.k-collection .sortable-fallback {
  opacity: .25 !important;
  overflow: hidden;
}

</style>
