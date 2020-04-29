<template>
  <k-section
    v-bind="$props"
    :options="sectionOptions"
    type="pages"
    @option="onSectionOption"
  >
    <k-async-collection
      :help="help"
      :items="pages"
      :layout="layout"
      :sortable="sortable"
      @flag="onFlag"
      @option="onPageOption"
    />
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
    help: String,
    layout: String,
    pages: Function,
    sortable: Boolean
  },
  computed: {
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
    onFlag(item, itemIndex) {
      this.$emit("flag", item, itemIndex);
    },
    onPageOption(option, page, pageIndex) {
      this.$emit("option", option, page, pageIndex);
    },
    onSectionOption(option) {
      this.$emit("option", option);
    }
  }
};
</script>
