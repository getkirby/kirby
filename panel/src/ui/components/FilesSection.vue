<template>
  <k-section
    v-bind="$props"
    :options="options"
    type="files"
    @option="onSectionOption"
  >
    <k-async-collection
      :help="help"
      :items="files"
      :layout="layout"
      :sortable="sortable"
      @flag="onFlag"
      @option="onFileOption"
    />
  </k-section>
</template>

<script>
import Section from "./Section.vue";

export default {
  extends: Section,
  inheritAttrs: false,
  props: {
    help: String,
    layout: String,
    files: Function,
    sortable: Boolean
  },
  computed: {
    options() {
      return [
        {
          icon: "upload",
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
    onFileOption(option, page, pageIndex) {
      this.$emit("option", option, page, pageIndex);
    },
    onSectionOption(option) {
      this.$emit("option", option);
    }
  }
};
</script>
