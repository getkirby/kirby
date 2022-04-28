<template>
  <div class="k-bubbles-field-preview" :class="$options.class">
    <k-bubbles :bubbles="bubbles" />
  </div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
  inheritAttrs: false,
  mixins: [FieldPreview],
  props: {
    value: Array
  },
  computed: {
    bubbles() {
      return this.value;
    }
  }
};
</script>

<style>
.k-bubbles-field-preview {
  padding: 0.325rem 0.75rem;
}
</style>
