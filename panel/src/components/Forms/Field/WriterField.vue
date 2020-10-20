<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-writer-field"
  >
    <k-writer
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
    counter: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    counterOptions() {
      if (this.value === null || this.disabled || this.counter === false) {
        return false;
      }
      return {
        count: this.value ? this.value.length : 0,
        min: this.minlength,
        max: this.maxlength
      };
    }
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
