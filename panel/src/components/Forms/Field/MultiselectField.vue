<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-multiselect-field"
    @blur="blur"
    @keydown.native.enter.prevent="focus"
  >
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      type="multiselect"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import MultiselectInput from "../Input/MultiselectInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...MultiselectInput.props,
    counter: {
      type: Boolean,
      default: true
    },
    icon: {
      type: String,
      default: "angle-down"
    }
  },
  computed: {
    // REFACTOR: DRY the following - same in TagsField
    counterOptions() {
      if (this.value === null || this.disabled || this.counter === false) {
        return false;
      }

      return {
        count: this.value && Array.isArray(this.value) ? this.value.length : 0,
        min: this.min,
        max: this.max
      };
    }
  },
  mounted() {
    this.$refs.input.$el.setAttribute('tabindex', 0);
  },
  methods: {
    blur(e) {
      this.$refs.input.blur(e);
    },
    focus() {
      this.$refs.input.focus();
    }
  },
};
</script>
