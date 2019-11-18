<template>
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
}
</script>

<style lang="scss">
.k-toggle-field-preview label {
  padding: 0 .25rem 0 .75rem;
  display: flex;
  height: 38px;
  cursor: pointer;
  overflow: hidden;
  white-space: nowrap;
}
.k-toggle-field-preview .k-toggle-input-label {
  padding-left: .5rem;
}
</style>
