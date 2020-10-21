<template>
  <k-field
    :input="_uid"
    :counter="false"
    v-bind="$props"
    class="k-writer-field"
  >
    <k-writer
      ref="input"
      :breaks="true"
      :value="value"
      class="k-writer-field-input"
      @input="$emit('input', $event)"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>

<style lang="scss">
.k-writer-field-input {
  line-height: 1.5em;
  background: #fff;
  padding: .325rem .5rem;
  border: 1px solid $color-border;
}
.k-writer-field-input:focus-within {
  border: 1px solid $color-focus;
  outline: 2px solid $color-focus-outline;
}
</style>
