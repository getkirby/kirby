<template>
  <k-item-image class="k-image-field-preview" :image="value" layout="list" />
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
  inheritAttrs: false,
  mixins: [FieldPreview],
  props: {
    value: [Object]
  }
};
</script>
