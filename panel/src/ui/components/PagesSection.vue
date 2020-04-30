<template>
  <k-section
    v-bind="$props"
    :options="sectionOptions"
    type="pages"
    @option="onSectionOption"
  >
    <k-async-collection
      :empty="empty || $t('pages.empty')"
      :help="help"
      :image="image"
      :items="pages"
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
      @option="onPageOption"
    />
    <slot />
  </k-section>
</template>

<script>
import Section from "./Section.vue";

export default {
  extends: Section,
  inheritAttrs: false,
  props: {
    add: {
      type: Boolean,
      default: false
    },
    empty: [String, Object],
    help: [Boolean, String],
    image: {
      type: [Boolean, Object],
      default: true,
    },
    info: [Boolean, String],
    layout: String,
    limit: {
      type: Number,
      default: 20,
    },
    page: {
      type: Number,
      default: 1,
    },
    pages: Function,
    sortable: Boolean
  },
  computed: {
    listeners() {
      if (this.add) {
        return {
          empty: this.onEmpty
        }
      }

      return {};
    },
    sectionOptions() {
      if (this.add === false) {
        return [];
      }

      return [
        {
          icon: "add",
          option: "add",
          text: "Add",
        },
      ];
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
    onPageOption(option, page, pageIndex) {
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
