<template>
  <k-section
    v-bind="$props"
    :options="options"
    type="pages"
    @option="onSectionOption"
  >
    <k-async-collection
      :empty="emptyOptions"
      :help="help"
      :icon="icon"
      :image="image"
      :items="items"
      :layout="layout"
      :loader="{
        info,
        ratio: image.ratio
      }"
      :pagination="{
        page,
        limit
      }"
      :sortable="sortable"
      v-on="listeners"
      @flag="onFlag"
      @option="onItemOption"
    />
    <slot />
  </k-section>
</template>

<script>
import Section from "./Section.vue";
import items from "@/ui/mixins/items.js";

export default {
  extends: Section,
  mixins: [items],
  inheritAttrs: false,
  props: {
    add: {
      type: Boolean,
      default: false
    },
    help: [Boolean, String],
    info: [Boolean, String],
    items: Function,
    limit: {
      type: Number,
      default: 20,
    },
    page: {
      type: Number,
      default: 1,
    },
    sortable: Boolean
  },
  computed: {
    defaultEmpty() {
      return {};
    },
    emptyOptions() {
      if (this.empty) {
        if (typeof this.empty === "string") {
          return {
            ...this.defaultEmpty,
            text: this.empty
          };
        }

        return {
          ...this.defaultEmpty,
          ...this.empty
        };
      }

      return this.defaultEmpty;
    },
    listeners() {
      if (this.add) {
        return {
          empty: this.onEmpty
        }
      }

      return {};
    },
    type() {
      return "model";
    }
  },
  methods: {
    onEmpty(event) {
      this.$emit("empty", event);
      this.onSectionOption("add");
    },
    onFlag(item, itemIndex) {
      this.$emit("flag", item, itemIndex);
    },
    onItemOption(option, page, pageIndex) {
      this.$emit("option", option, page, pageIndex);
    },
    onSectionOption(option) {
      this.$emit("option", option);

      switch (option) {
        case "add":
          this.$emit("add");
          break;
      }
    }
  }
};
</script>
