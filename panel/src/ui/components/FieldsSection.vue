<template>
  <k-section
    :name="name"
    type="fields"
  >
    <k-form
      :fields="fields"
      :value="values"
      :disabled="disabled"
      @input="onInput"
      @submit="onSubmit"
    />
  </k-section>
</template>

<script>
import Section from "./Section.vue";

export default {
  extends: Section,
  inheritAttrs: false,
  props: {
    disabled: Boolean,
    fields: Object,
    values: Object,
  },
  methods: {
    onInput(values) {
      this.$emit("input", values);
    },
    onSubmit(values) {
      this.$emit("submit", values);
    }
  }
};
</script>
