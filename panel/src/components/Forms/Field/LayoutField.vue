<template>
  <k-field
    v-bind="$props"
    class="k-layout-field"
  >
    <k-block-layouter
      :endpoints="endpoints"
      :fieldsets="fieldsets"
      :layouts="layouts"
      :value="value"
      @input="$emit('input', $event)"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Layouts from "@/components/Layouter/Layouts.vue";
import Layouter from "@/components/Layouter/Layouter.vue";

export default {
  components: {
    "k-block-layouts": Layouts,
    "k-block-layouter": Layouter
  },
  inheritAttrs: false,
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

