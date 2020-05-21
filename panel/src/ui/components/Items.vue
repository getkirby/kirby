<template>
  <k-draggable
    class="k-items"
    :class="'k-' + itemLayout + '-items'"
    :handle="true"
    :options="dragOptions"
    :data-layout="layout"
    :data-size="size"
    :list="items"
    @change="onSortChange"
    @end="onSortEnd"
  >
    <slot>
      <k-item
        v-for="(item, itemIndex) in items"
        :key="item.id || itemIndex"
        v-bind="item"
        :icon="iconSettings(item)"
        :image="imageSettings(item)"
        :layout="itemLayout"
        :sortable="sortable"
        @click="onItem(item, itemIndex)"
        @flag="onFlag(item, itemIndex)"
        @option="$emit('option', $event, item, itemIndex)"
      />
    </slot>
  </k-draggable>
</template>

<script>
import items from "@/ui/mixins/items.js";

export default {
  inheritAttrs: false,
  mixins: [items],
  props: {
    items: {
      type: Array,
      default() {
        return [];
      }
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
    iconSettings(item) {
      let globalSettings = this.icon;
      let localSettings  = item.icon;

      if (globalSettings === false) {
        return false;
      }

      if (localSettings === false) {
        return false;
      }

      if (typeof globalSettings !== "object") {
        globalSettings = {};
      }

      if (typeof localSettings !== "object") {
        localSettings = {};
      }

      return {
        // individual settings
        type: localSettings.type || globalSettings.type,
        back: localSettings.back || globalSettings.back,
      };
    },
    imageSettings(item) {
      let globalSettings = this.image;
      let localSettings  = item.image;

      if (globalSettings === false) {
        return false;
      }

      if (localSettings === false) {
        return false;
      }

      if (typeof globalSettings !== "object") {
        globalSettings = {};
      }

      if (typeof localSettings !== "object") {
        localSettings = {};
      }

      return {
        // global settings
        cover: globalSettings.cover || localSettings.cover,
        ratio: globalSettings.ratio || localSettings.ratio,
        // individual settings
        back: localSettings.back || globalSettings.back,
        url: localSettings.url
      };
    },
    onFlag(item, itemIndex) {
      this.$emit('flag', item, itemIndex)
    },
    onItem(item, itemIndex) {
      this.$emit('item', item, itemIndex)
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

  /**
    Making sure card doesn't break layout if
    in a parent narrower than 12rem

    TODO: refactor once min() is supported by all our browsers
    since LibSASS has its issues with min() we need to be tricky
    */
  --min: 12rem;
  grid-template-columns: repeat(auto-fill, minmax(var(--min), 1fr));
  grid-template-columns: repeat(auto-fill, minmax(#{"min(var(--min), 100%)"}, 1fr));
}

@media screen and (min-width: $breakpoint-sm) {
  .k-card-items[data-size="tiny"] {
    --min: 8rem;
  }
  .k-card-items[data-size="small"] {
    --min: 10rem;
  }
  .k-card-items[data-size="medium"] {
    --min: 16rem;
  }
  .k-card-items[data-size="large"],
  .k-card-items[data-size="huge"] {
    grid-template-columns: 1fr;
  }
}

@media screen and (min-width: $breakpoint-md) {
  .k-card-items[data-size="large"] {
    --min: 32rem;
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
