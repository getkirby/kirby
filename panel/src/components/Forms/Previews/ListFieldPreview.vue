<template>
  <!-- eslint-disable-next-line vue/no-v-html -->
  <div class="k-list-field-preview" v-html="value" />
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
.k-list-field-preview {
  padding: .325rem .75rem;
  line-height: 1.5em;
}
.k-list-field-preview ul,
.k-list-field-preview ol {
  margin-left: 1rem;
}
.k-list-field-preview ul > li {
  list-style: disc;
}
.k-list-field-preview ol ul > li,
.k-list-field-preview ul ul > li {
  list-style: circle;
}
.k-list-field-preview ol > li {
  list-style: decimal;
}
.k-list-field-preview ol > li::marker {
  color: var(--color-gray-500);
  font-size: var(--text-xs);
}
</style>
