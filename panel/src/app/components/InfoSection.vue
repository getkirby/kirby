<template>
  <k-section
    :name="name"
    type="info"
  >
    <k-headline class="k-info-section-headline mb-3">
      {{ label || headline }}
    </k-headline>
    <k-box :theme="theme">
      <k-text v-html="text" />
    </k-box>
  </k-section>
</template>

<script>
import Section from "./Section.vue";

export default {
  extends: Section,
  inheritAttrs: false,
  props: {
    text: String,
    theme: {
      type: String,
      default: "info"
    }
  }
};
</script>
