<template>
  <kirby-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="kirby-tags-field"
  >
    <kirby-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      v-on="$listeners"
    />
  </kirby-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import TagsInput from "../Input/TagsInput.vue";

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
.kirby-field-counter {
  display: none;
}
.kirby-text-field:focus-within .kirby-field-counter {
  display: block;
}
</style>
