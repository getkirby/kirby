<template>
  <p class="k-tags-field-preview">
    {{ column.before }}
    <k-tag v-for="tag in tags" :key="tag">{{ tag }}</k-tag>
    {{ column.after }}
  </p>
</template>

<script>
export default {
  props: {
    column: Object,
    value: [Array, String]
  },
  computed: {
    tags() {
      if (Array.isArray(this.value)) {
        return this.value.map((tag) => tag.text || tag);
      }

      return this.value.split(",");
    }
  }
};
</script>

<style>
.k-tags-field-preview {
  display: flex;
  padding: 0 0.75rem;
  pointer-events: none;
}
.k-tags-field-preview .k-tag {
  font-size: var(--text-xs);
  margin-inline-end: 0.25rem;
}
.k-tags-field-preview .k-tag-text {
  padding: 0.25rem 0.5rem 0.275rem;
  white-space: nowrap;
}
</style>
