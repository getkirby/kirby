<template>
  <!-- eslint-disable-next-line vue/no-v-html -->
  <div class="k-writer-field-preview" v-html="value" />
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    value: String
  }
}
</script>

<style>
.k-writer-field-preview {
  padding: .325rem .75rem;
  line-height: 1.5em;
}
.k-writer-field-preview p:not(:last-child) {
  margin-bottom: 1.5em;
}
</style>
