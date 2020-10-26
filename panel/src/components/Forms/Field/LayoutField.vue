<template>
  <k-field
    v-bind="$props"
    class="k-layout-field"
  >
    <k-block-layouts
      :fieldsets="fieldsets"
      :layouts="layouts"
      :value="value"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Layouts from "@/components/Layouter/Layouts.vue";

export default {
  inheritAttrs: false,
  components: {
    "k-block-layouts": Layouts
  },
  props: {
    ...Field.props,
    empty: String,
    fieldsets: Object,
    layouts: {
      type: Array,
      default() {
        return [
          ["1/1"]
        ];
      }
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  }
};
</script>

