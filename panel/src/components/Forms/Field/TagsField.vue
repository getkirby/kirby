<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-tags-field"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="tags"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import TagsInput from "../Input/TagsInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-tags-input>` for additional information.
 */
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

