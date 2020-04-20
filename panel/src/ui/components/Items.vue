<template>
  <k-draggable
    class="k-items"
    :class="'k-' + itemLayout + '-items'"
    :handle="true"
    :options="dragOptions"
    :data-layout="layout"
    :list="items"
    @change="onSortChange"
    @end="onSortEnd"
  >
    <slot>
      <k-item
        v-for="(item, itemIndex) in items"
        v-bind="item"
        :key="itemIndex"
        :layout="itemLayout"
        :sortable="sortable"
        @flag="onFlag(item, itemIndex)"
        @option="$emit('option', $event, item, itemIndex)"
      />
    </slot>
  </k-draggable>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    items: Array,
    layout: {
      type: String,
      default: "list"
    },
    sortable: Boolean,
  },
  computed: {
    dragOptions() {
      return {
        sort: this.sortable,
        disabled: this.sortable === false,
        draggable: ".k-item"
      };
    },
    itemLayout() {
      const layouts = {
        list: "list",
        card: "card",
        cards: "card",
        cardlet: "cardlet",
        cardlets: "cardlet"
      };

      return layouts[this.layout] || "list";
    },
  },
  methods: {
    onFlag(item, itemIndex) {
      this.$emit('flag', item, itemIndex)
    },
    onOption(option, item, itemIndex) {
      this.$emit('option', option, item, itemIndex)
    },
    onSortChange(event) {
      this.$emit("sortChange", this.items, event);
    },
    onSortEnd(event) {
      this.$emit("sort", this.items, event);
    }
  }
};
</script>

<style lang="scss">
/**
 * Cards
 */
.k-card-items {
  display: grid;
  grid-gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
  /**
    Making sure card doesn't break layout if
    in a parent narrower than 12rem

    TODO: refactor once min() is supported by all our browsers
    since LibSASS has its issues with min() we need to be tricky
    */
  grid-template-columns: repeat(auto-fill, minmax(#{"min(12rem, 100%)"}, 1fr));
}

@media screen and (min-width: $breakpoint-small) {
/**
 * Cards
 */
  .k-card-items[data-size="tiny"] {
    grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 12rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(10rem, 100%)"}, 1fr)
    );
  }
/**
 * Cards
 */
  .k-card-items[data-size="small"] {
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 16rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(16rem, 100%)"}, 1fr)
    );
  }
/**
 * Cards
 */
  .k-card-items[data-size="medium"] {
    grid-template-columns: repeat(auto-fill, minmax(24rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 24rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(24rem, 100%)"}, 1fr)
    );
  }
/**
 * Cards
 */
  .k-card-items[data-size="large"],
/**
 * Cards
 */
  .k-card-items[data-size="huge"] {
    grid-template-columns: 1fr;
  }
}

@media screen and (min-width: $breakpoint-medium) {
/**
 * Cards
 */
  .k-card-items[data-size="large"] {
    grid-template-columns: repeat(auto-fill, minmax(32rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 32rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
     */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(32rem, 100%)"}, 1fr)
    );
  }
}

/**
 * Cardlets
 */
.k-cardlet-items {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
  grid-gap: .5rem;
}

/**
 * List
 */
.k-list-items .k-list-item:not(:last-child) {
  margin-bottom: 2px;
}

</style>
