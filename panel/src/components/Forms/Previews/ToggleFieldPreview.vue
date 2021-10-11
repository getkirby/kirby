<template>
  <!-- eslint-disable vue/no-mutating-props -->
  <k-input
    v-model="value"
    :text="text"
    class="k-toggle-field-preview"
    type="toggle"
    @input="$emit('input', $event)"
  />
</template>

<script>
export default {
  props: {
    field: Object,
    value: Boolean,
    column: Object
  },
  computed: {
    text() {
      return this.column.text !== false ? this.field.text : null;
    }
  }
};
</script>

<style>
.k-toggle-field-preview label {
  padding: 0 0.25rem 0 0.75rem;
  display: flex;
  height: 38px;
  cursor: pointer;
  overflow: hidden;
  white-space: nowrap;
}
.k-toggle-field-preview .k-toggle-input-label {
  padding-inline-start: 0.5rem;
}

[data-align="right"] .k-toggle-field-preview .k-toggle-input-label {
  padding-inline-end: 0.5rem;
}

.k-toggle-field-preview .k-toggle-input {
  padding-block: 0;
  padding-inline: 0.75rem 0.25rem;
}

[data-align="right"] .k-toggle-field-preview .k-toggle-input {
  flex-direction: row-reverse;
  padding-inline: 0.25rem 0.75rem;
}
</style>
