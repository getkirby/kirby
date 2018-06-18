<template>
  <div :data-layout="layout" class="kirby-collection">
    <kirby-draggable
      :list="items"
      :options="dragOptions"
      :element="elements.list"
      :move="onMove"
      @change="$emit('change', $event)"
      @end="onEnd"
    >
      <component
        v-for="(item, index) in items"
        :is="elements.item"
        :key="index"
        v-bind="item"
        class="kirby-draggable-item"
        @action="$emit('action', item, $event)"
      />
    </kirby-draggable>

    <kirby-pagination
      v-if="pagination !== false && paginationOptions.hide !== true"
      v-bind="paginationOptions"
      @paginate="$emit('paginate', $event)"
    />
  </div>

</template>

<script>

export default {
  props: {
    group: String,
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
    draggable: Boolean,
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
  watch: {
    items() {
      this.list = this.items;
    },
    $props() {
      this.$forceUpdate();
    }
  },
  computed: {
    dragOptions() {
      return {
        sort: this.sortable,
        forceFallback: true,
        filter: ".disabled",
        delay: 1,
        draggable: ".kirby-draggable-item",
        disabled: !this.sortable && !this.draggable,
        group: {
          name: this.group,
        }

      };
    },
    elements() {
      const layouts = {
        cards: {
          list: "kirby-cards",
          item: "kirby-card"
        },
        list: {
          list: "kirby-list",
          item: "kirby-list-item"
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
  over: null,
  methods: {
    onEnd() {
      if (this.over) {
        this.over.removeAttribute("data-over");
      }
      this.$emit("sort", this.items);
    },
    onMove(evt) {

      if (this.over) {
        this.over.removeAttribute("data-over");
      }

      this.over = evt.to;
      this.over.setAttribute("data-over", true);

    }
  }
}
</script>

<style lang="scss">

.kirby-collection > *:not(:empty) {
  min-height: 38px;
  margin-bottom: 2px;
}
.kirby-collection > *:empty {
  position: relative;
  border: 1px solid $color-border;
  border-radius: $border-radius;
  background: $color-inset;
  margin-bottom: 2px;
  height: 38px;
}

.kirby-collection .kirby-cards[data-over] .kirby-box,
.kirby-collection .kirby-list[data-over] .kirby-box {
  display: none;
}
.kirby-collection[data-layout="cards"] .kirby-cards {
}

.kirby-collection .sortable-ghost {
  background: $color-inset;
  height: auto;
  outline: 2px solid $color-focus;
}
.kirby-collection .kirby-list .sortable-ghost {
  height: 38px !important;
  margin-bottom: 2px;
}


.kirby-collection .sortable-ghost * {
  visibility: hidden;
}
</style>
