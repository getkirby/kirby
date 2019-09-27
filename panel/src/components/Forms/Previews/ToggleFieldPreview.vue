<template>
  <k-input
    v-model="value"
    :text="field.text"
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
  }
}
</script>

<style lang="scss">
.k-toggle-field-preview label {
  padding: 0 .25rem 0 .75rem;
  display: flex;
  height: 38px;
  cursor: pointer;
}
.k-toggle-field-preview .k-toggle-input-label {
  padding-left: .5rem;
}
</style>
