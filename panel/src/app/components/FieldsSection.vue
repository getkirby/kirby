<template>
  <k-section
    :name="name"
    type="fields"
  >
    <k-form
      :fields="fields"
      :value="value"
      :disabled="disabled || lock !== false"
      v-on="$listeners"
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
  }
};
</script>
