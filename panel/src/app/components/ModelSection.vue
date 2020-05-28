<template>
  <k-section
    v-bind="$props"
    :options="options"
    :type="type"
    @option="onSectionOption"
  >
    <k-async-collection
      :empty="emptyOptions"
      :help="help"
      :items="items"
      :layout="layout"
      :loader="{
        info,
        ratio: preview.ratio
      }"
      :pagination="{
        page,
        limit
      }"
      :preview="preview"
      :sortable="sortable"
      v-on="listeners"
      @flag="onFlag"
      @option="onItemOption"
    />

    <slot name="footer" />
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
    sortable: Boolean,
    type: String
  },
  computed: {
    emptyOptions() {
      if (typeof this.empty === "string") {
        return {
          text: this.empty
        };
      }

      return this.empty;
    },
    listeners() {
      if (this.add) {
        return {
          empty: this.onEmpty
        }
      }

      return {};
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
    onItemOption(option, model, modelIndex) {
      this.$emit("option", option, model, modelIndex);
    },
    onSectionOption(option) {
      this.$emit("option", option);
    }
  }
};
</script>
