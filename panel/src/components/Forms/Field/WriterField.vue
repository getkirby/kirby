<template>
  <k-field
    :input="_uid"
    :counter="false"
    v-bind="$props"
    class="k-writer-field"
  >
    <k-input
      v-bind="$props"
      :after="after"
      :before="before"
      :icon="icon"
      theme="field"
    >
      <k-writer
        ref="input"
        v-bind="$props"
        :value="value"
        class="k-writer-field-input"
        @input="$emit('input', $event)"
      />
    </k-input>
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import Writer from "@/components/Writer/Writer.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...Writer.props,
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>

<style>
.k-writer-field-input {
  line-height: 1.5em;
  padding: .375rem .5rem;
}
</style>
