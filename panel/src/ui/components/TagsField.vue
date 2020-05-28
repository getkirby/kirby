<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="tags"
      class="px-1 pt-1 pb-0"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "./Field.vue";
import Input from "./Input.vue";
import TagsInput from "./TagsInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...TagsInput.props,
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
        count: this.value && Array.isArray(this.value) ? this.value.length : 0,
        min: this.min,
        max: this.max,
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
.k-tags-field:not(:focus-within) .k-tags-input-element {
  min-width: 0;
}
</style>
