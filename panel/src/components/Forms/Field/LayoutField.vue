<template>
  <k-field v-bind="$props" class="k-layout-field">
    <k-block-layouts v-bind="$props" @input="$emit('input', $event)" />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import Layouts from "@/components/Layouter/Layouts.vue";

export default {
  components: {
    "k-block-layouts": Layouts
  },
  mixins: [Field],
  inheritAttrs: false,
  props: {
    empty: String,
    fieldsetGroups: Object,
    fieldsets: Object,
    layouts: {
      type: Array,
      default() {
        return [["1/1"]];
      }
    },
    settings: Object,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  }
};
</script>
